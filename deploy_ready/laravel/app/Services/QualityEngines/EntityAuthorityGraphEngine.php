<?php

namespace App\Services\QualityEngines;

use App\Models\ContentNode;
use App\Models\EntityAuthorityGraph;
use App\Models\EntityRelationship;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EntityAuthorityGraphEngine
{
    protected array $entityTypes = [
        'city', 'service', 'tool', 'technology',
        'company', 'industry', 'trend', 'person',
    ];

    public function rebuildGraph(int $chunkSize = 100): array
    {
        $stats = [
            'entities_extracted' => 0,
            'relationships_created' => 0,
            'authority_propagated' => 0,
        ];

        ContentNode::whereNotNull('body_content')
            ->chunk($chunkSize, function ($contents) use (&$stats) {
                foreach ($contents as $content) {
                    $entities = $this->extractEntities($content);
                    foreach ($entities as $entity) {
                        $graphEntity = $this->upsertEntity($entity);
                        $stats['entities_extracted']++;

                        $rels = $this->extractRelationships($content, $graphEntity);
                        foreach ($rels as $rel) {
                            $this->createRelationship($graphEntity->id, $rel);
                            $stats['relationships_created']++;
                        }
                    }
                }
            });

        $stats['authority_propagated'] = $this->propagateAuthority();

        return $stats;
    }

    public function analyzeContentEntities(ContentNode $content): array
    {
        return $this->extractEntities($content);
    }

    protected function extractEntities(ContentNode $content): array
    {
        $entities = [];
        $body = strip_tags($content->body_content ?? '');
        $bodyLower = mb_strtolower($body);
        $titleLower = mb_strtolower($content->seo_title ?? '');

        if ($content->location) {
            $entities[] = [
                'type' => 'city',
                'name' => $content->location->name,
                'slug' => Str::slug($content->location->name),
                'description' => "Location entity: {$content->location->name}",
                'context' => 'content_node_location',
            ];
        }

        if ($content->taxonomy) {
            $taxonomyName = $content->taxonomy->name;
            $parentName = $content->taxonomy->parent?->name;

            $entities[] = [
                'type' => 'service',
                'name' => $taxonomyName,
                'slug' => Str::slug($taxonomyName),
                'description' => "Service entity derived from taxonomy: {$taxonomyName}",
                'context' => 'content_node_taxonomy',
            ];

            if ($parentName) {
                $entities[] = [
                    'type' => 'industry',
                    'name' => $parentName,
                    'slug' => Str::slug($parentName),
                    'description' => "Industry entity derived from parent taxonomy: {$parentName}",
                    'context' => 'content_node_parent_taxonomy',
                ];
            }
        }

        $entityPatterns = $this->getEntityPatterns();
        foreach ($entityPatterns as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match_all('/' . preg_quote($pattern, '/') . '/iu', $bodyLower, $matches)) {
                    $uniqueMatches = array_unique($matches[0]);
                    foreach ($uniqueMatches as $match) {
                        $match = trim($match);
                        if (strlen($match) > 2 && strlen($match) < 100) {
                            $entities[] = [
                                'type' => $type,
                                'name' => ucwords($match),
                                'slug' => Str::slug($match),
                                'description' => "{$type} entity mentioned in content",
                                'context' => 'body_mention',
                            ];
                        }
                    }
                }
            }
        }

        $entities = $this->deduplicateEntities($entities);

        return array_slice($entities, 0, 30);
    }

    protected function getEntityPatterns(): array
    {
        return [
            'tool' => [
                'tool', 'software', 'platform', 'application', 'app',
                'sdk', 'api', 'framework', 'library', 'module',
            ],
            'technology' => [
                'technology', 'tech', 'digital', 'automation', 'ai',
                'machine learning', 'artificial intelligence', 'blockchain',
                'cloud', 'saas', 'paas', 'iaas', 'serverless',
            ],
            'company' => [
                'inc', 'llc', 'ltd', 'corp', 'corporation', 'company',
                'gmbh', 'limited', 'group', 'solutions',
            ],
            'trend' => [
                'trend', 'growing', 'emerging', 'rising', 'popular',
                'increasing', 'surge', 'boom', 'shift', 'evolution',
            ],
            'person' => [
                'expert', 'specialist', 'professional', 'consultant',
                'analyst', 'researcher', 'scientist', 'engineer',
                'manager', 'director', 'ceo', 'founder', 'author',
            ],
        ];
    }

    protected function deduplicateEntities(array $entities): array
    {
        $seen = [];
        $deduped = [];

        foreach ($entities as $entity) {
            $key = $entity['type'] . ':' . $entity['slug'];
            if (!isset($seen[$key])) {
                $seen[$key] = true;
                $deduped[] = $entity;
            }
        }

        return $deduped;
    }

    protected function upsertEntity(array $entityData): EntityAuthorityGraph
    {
        return EntityAuthorityGraph::firstOrCreate(
            ['entity_slug' => $entityData['slug'], 'entity_type' => $entityData['type']],
            [
                'entity_name' => $entityData['name'],
                'entity_type' => $entityData['type'],
                'entity_slug' => $entityData['slug'],
                'description' => $entityData['description'] ?? null,
                'entity_authority_score' => 10,
                'topical_relevance_score' => 10,
                'mention_count' => 1,
            ]
        );
    }

    protected function extractRelationships(ContentNode $content, EntityAuthorityGraph $source): array
    {
        $relationships = [];
        $body = strip_tags($content->body_content ?? '');

        $relatedEntities = EntityAuthorityGraph::where('entity_slug', '!=', $source->entity_slug)
            ->orWhere('entity_type', '!=', $source->entity_type)
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $currentEntitySlugs = [$source->entity_slug];

        if ($content->location) {
            $currentEntitySlugs[] = Str::slug($content->location->name);
        }

        if ($content->taxonomy) {
            $currentEntitySlugs[] = Str::slug($content->taxonomy->name);
        }

        foreach ($relatedEntities as $target) {
            if ($target->id === $source->id) {
                continue;
            }

            $strength = 10;

            if (in_array($target->entity_slug, $currentEntitySlugs)) {
                $strength += 30;
            }

            if (mb_strpos(mb_strtolower($body), mb_strtolower($target->entity_name)) !== false) {
                $strength += 20;
            }

            if ($source->entity_type === 'city' && $target->entity_type === 'service') {
                $strength += 25;
            }

            $coOccurrences = DB::table('entity_authority_graph as eag')
                ->join('entity_relationships as er', function ($join) use ($source, $target) {
                    $join->on('eag.id', '=', 'er.source_entity_id')
                        ->where('er.target_entity_id', $target->id);
                })
                ->where('eag.id', $source->id)
                ->count();

            if ($coOccurrences > 0) {
                $strength += min(20, $coOccurrences * 5);
            }

            $strength = min(100, $strength);

            if ($strength >= config('quality-engine.entity_graph.min_relationship_strength', 10) * 100) {
                $relationships[] = [
                    'target_entity_id' => $target->id,
                    'type' => $this->determineRelationshipType($source->entity_type, $target->entity_type),
                    'strength' => $strength,
                    'context' => "Discovered via content analysis of: {$content->seo_title}",
                ];
            }
        }

        return $relationships;
    }

    protected function determineRelationshipType(string $sourceType, string $targetType): string
    {
        $typeMap = [
            'city' => ['service' => 'located_in', 'company' => 'located_in', 'person' => 'located_in'],
            'service' => ['city' => 'serves', 'tool' => 'uses', 'technology' => 'uses', 'company' => 'offered_by'],
            'tool' => ['service' => 'used_by', 'technology' => 'built_on', 'company' => 'developed_by'],
            'technology' => ['tool' => 'powers', 'service' => 'enables', 'industry' => 'transforms'],
            'company' => ['service' => 'provides', 'tool' => 'develops', 'city' => 'headquartered_in'],
            'industry' => ['trend' => 'impacts', 'technology' => 'adopts', 'company' => 'includes'],
            'trend' => ['industry' => 'affects', 'technology' => 'drives', 'person' => 'led_by'],
            'person' => ['company' => 'works_at', 'service' => 'expert_in', 'trend' => 'follows'],
        ];

        return $typeMap[$sourceType][$targetType] ?? 'related_to';
    }

    protected function createRelationship(int $sourceId, array $relData): void
    {
        EntityRelationship::updateOrCreate(
            [
                'source_entity_id' => $sourceId,
                'target_entity_id' => $relData['target_entity_id'],
                'relationship_type' => $relData['type'],
            ],
            [
                'relationship_strength' => $relData['strength'],
                'context' => $relData['context'] ?? null,
            ]
        );
    }

    public function propagateAuthority(): int
    {
        $depth = config('quality-engine.entity_graph.authority_propagation_depth', 3);
        $decay = config('quality-engine.entity_graph.propagation_decay', 0.5);
        $updated = 0;

        $entities = EntityAuthorityGraph::all();

        foreach ($entities as $entity) {
            $inboundScore = 0;
            $visited = [$entity->id => true];

            $this->traverseInbound($entity->id, $inboundScore, $decay, 1, $depth, $visited);

            $baseAuthority = $entity->mention_count * 5;
            $completeAuthority = min(100, $baseAuthority + $inboundScore);

            if (abs($entity->entity_authority_score - $completeAuthority) > 0.01) {
                $entity->update(['entity_authority_score' => round($completeAuthority, 2)]);
                $updated++;
            }

            $outboundLinks = EntityRelationship::where('source_entity_id', $entity->id)->count();
            $inboundLinks = EntityRelationship::where('target_entity_id', $entity->id)->count();
            $entity->update([
                'inbound_link_count' => $inboundLinks,
                'outbound_link_count' => $outboundLinks,
            ]);
        }

        return $updated;
    }

    protected function traverseInbound(int $entityId, float &$score, float $decay, int $currentDepth, int $maxDepth, array &$visited): void
    {
        if ($currentDepth > $maxDepth) {
            return;
        }

        $inbound = EntityRelationship::where('target_entity_id', $entityId)
            ->where('relationship_strength', '>', 20)
            ->get();

        foreach ($inbound as $rel) {
            if (isset($visited[$rel->source_entity_id])) {
                continue;
            }

            $visited[$rel->source_entity_id] = true;
            $contribution = ($rel->relationship_strength / 100) * pow($decay, $currentDepth - 1);
            $score += $contribution;

            $this->traverseInbound($rel->source_entity_id, $score, $decay, $currentDepth + 1, $maxDepth, $visited);
        }
    }

    public function getSemanticNeighborhood(int $entityId, int $depth = 2): array
    {
        $neighbors = [];
        $visited = [$entityId => true];

        $this->traverseNeighborhood($entityId, $neighbors, $visited, 0, $depth);

        return $neighbors;
    }

    protected function traverseNeighborhood(int $entityId, array &$neighbors, array &$visited, int $currentDepth, int $maxDepth): void
    {
        if ($currentDepth >= $maxDepth) {
            return;
        }

        $relationships = EntityRelationship::where('source_entity_id', $entityId)
            ->orWhere('target_entity_id', $entityId)
            ->with(['source', 'target'])
            ->get();

        foreach ($relationships as $rel) {
            $neighborId = $rel->source_entity_id === $entityId
                ? $rel->target_entity_id
                : $rel->source_entity_id;

            if (isset($visited[$neighborId])) {
                continue;
            }

            $visited[$neighborId] = true;
            $neighbor = $rel->source_entity_id === $entityId ? $rel->target : $rel->source;

            $neighbors[] = [
                'entity' => $neighbor,
                'relationship_type' => $rel->relationship_type,
                'relationship_strength' => $rel->relationship_strength,
                'depth' => $currentDepth + 1,
            ];

            $this->traverseNeighborhood($neighborId, $neighbors, $visited, $currentDepth + 1, $maxDepth);
        }
    }

    public function computeTopicalRelevance(): int
    {
        $updated = 0;
        $entities = EntityAuthorityGraph::all();

        foreach ($entities as $entity) {
            $relationships = EntityRelationship::where('source_entity_id', $entity->id)
                ->orWhere('target_entity_id', $entity->id)
                ->get();

            if ($relationships->isEmpty()) {
                continue;
            }

            $avgStrength = $relationships->avg('relationship_strength');
            $totalConnections = $relationships->count();
            $typeDiversity = $relationships->pluck('relationship_type')->unique()->count();

            $relevance = ($avgStrength * 0.4) + (min(100, $totalConnections * 10) * 0.3) + (min(100, $typeDiversity * 20) * 0.3);
            $relevance = min(100, $relevance);

            if (abs($entity->topical_relevance_score - $relevance) > 0.01) {
                $entity->update(['topical_relevance_score' => round($relevance, 2)]);
                $updated++;
            }
        }

        return $updated;
    }
}
