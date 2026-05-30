<div class="p-4">
    <div class="mb-2">
        <span class="text-sm text-gray-400">Key:</span>
        <code class="block mt-1 p-2 bg-gray-900 rounded font-mono text-green-400">{{ $record->key }}</code>
    </div>
    <div>
        <span class="text-sm text-gray-400">Value:</span>
        <code class="block mt-1 p-2 bg-gray-900 rounded font-mono text-yellow-400 break-all">{{ $record->value ?? '(empty)' }}</code>
    </div>
    @if($record->description)
    <div class="mt-3">
        <span class="text-sm text-gray-400">Description:</span>
        <p class="mt-1 text-gray-300">{{ $record->description }}</p>
    </div>
    @endif
</div>