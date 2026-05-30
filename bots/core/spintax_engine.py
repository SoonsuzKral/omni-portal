"""
spintax_engine.py — Gelişmiş Spintax Motoru
============================================
Kelime eş anlamlıları, cümle varyasyonları
ve spintax sözdizimi çözümleyici.
"""

import random
import re
import hashlib

SPINTAX_MAP = {
    "hizmet": ["servis", "destek", "yardım", "çözüm", "bakım"],
    "firma": ["şirket", "kuruluş", "işletme", "kurum", "marka"],
    "profesyonel": ["uzman", "deneyimli", "tecrübeli", "kalifiye", "yetenekli"],
    "kaliteli": ["başarılı", "güvenilir", "nitelikli", "seçkin", "premium"],
    "en iyi": ["en kaliteli", "en başarılı", "en güvenilir", "en uygun", "en seçkin"],
    "uygun fiyat": ["ekonomik", "hesaplı", "bütçe dostu", "avantajlı", "makul"],
    "geniş": ["kapsamlı", "zengin", "çeşitli", "full", "eksiksiz"],
    "detaylı": ["kapsamlı", "ayrıntılı", "etraflı", "derinlemesine", "tüm yönleriyle"],
    "hızlı": ["pratik", "çabuk", "süratli", "acil", "ivedi"],
    "güvenilir": ["itibarlı", "sağlam", "emin", "garantili", "referanslı"],
    "kolay": ["basit", "rahat", "pratik", "kullanışlı", "konforlu"],
    "memnuniyet": ["tatmin", "mutluluk", "hoşnutluk", "beğeni", "takdir"],
    "bulabilirsiniz": ["erişebilirsiniz", "ulaşabilirsiniz", "edinebilirsiniz", "inceleyebilirsiniz", "görebilirsiniz"],
    "ihtiyaç": ["gereksinim", "talep", "beklenti", "istek", "gerek"],
    "bölge": ["mıntıka", "yöre", "civar", "saha", "mevki"],
    "merkezli": ["odaklı", "ağırlıklı", "temelli", "eksenli", "dayalı"],
    "sayfa": ["platform", "portal", "kaynak", "adres", "rehber"],
    "müşteri": ["kullanıcı", "ziyaretçi", "danışan", "alıcı", "tüketici"],
    "için": ["amacıyla", "üzere", "dolayı", "niyetiyle", "hedefiyle"],
    "olarak": ["şekilde", "biçimde", "surette", "halinde", "niteliğinde"],
    "bulunmaktadır": ["yer almaktadır", "mevcuttur", "hizmet vermektedir", "faaliyet göstermektedir", "konumlanmıştır"],
    "amaç": ["hedef", "gaye", "ereği", "niyet", "maksat"],
    "teknoloji": ["teknolojik donanım", "modern cihazlar", "son sistem", "yenilikçi ekipman", "ileri teknoloji"],
    "sürekli": ["düzenli", "devamlı", "kesintisiz", "mütemadiyen", "periyodik"],
    "takip": ["izleme", "gözlem", "inceleme", "araştırma", "takip etme"],
    "yenilikçi": ["modern", "çağdaş", "ileri görüşlü", "vizyoner", "inovasyon"],
    "imkan": ["fırsat", "olanak", "seçenek", "alternatif", "opsiyon"],
    "avantaj": ["fayda", "kazanç", "yarar", "artı", "üstünlük"],
    "en güncel": ["en son", "güncel", "en yeni", "son durum"],
    "kapsamlı": ["detaylı", "geniş", "eksiksiz", "derinlemesine"],
    "önemli": ["mühim", "kritik", "hayati", "büyük", "ciddi"],
}

BODY_TEMPLATES = [
    (
        "<p>{il} şehrinde <strong>{keyword}</strong> hizmeti arayanlar için "
        "en kapsamlı rehberi hazırladık. {il} merkez ve tüm "
        "ilçelerindeki {keyword} firmaları, güncel fiyat listeleri ve "
        "müşteri değerlendirmeleri hakkında detaylı bilgiler "
        "bulabilirsiniz.</p>"
        "<p>{il}'de {keyword} konusunda uzmanlaşmış işletmeler, son "
        "teknoloji ekipmanlarla hizmet vermektedir. Profesyonel ekipler "
        "{keyword} alanında en kaliteli çözümleri sunarak müşteri "
        "memnuniyetini ön planda tutmaktadır.</p>"
        "<p>2026 yılı güncel {keyword} fiyatları ve kampanyaları için "
        "sitemizi düzenli olarak ziyaret edebilirsiniz. {il} halkına "
        "özel indirim fırsatlarını kaçırmayın.</p>"
    ),
    (
        "<p><strong>{keyword}</strong> ihtiyacınız için {il} bölgesinde "
        "aranan adres olduk. {il} sınırları içerisinde {keyword} "
        "alanında faaliyet gösteren tüm firmaları tek platformda "
        "topladık.</p>"
        "<p>Amacımız, {il} sakinlerine {keyword} konusunda doğru ve "
        "güvenilir bilgi sunmaktır. Sitemiz, {il}'nin her noktasında "
        "ihtiyaç duyduğunuz {keyword} hizmetine hızlıca "
        "ulaşmanızı sağlar.</p>"
        "<p>{il}'de {keyword} sektöründeki yenilikler, kampanyalar ve "
        "gelişmeler için bizi takipte kalın. Profesyonel ekibimiz "
        "{keyword} konusunda size en iyi hizmeti sunmaya hazırdır.</p>"
    ),
    (
        "<p>{il} lokasyonunda {keyword} arayışınızda en doğru "
        "noktaya geldiniz. {il} ilçelerindeki tüm {keyword} "
        "seçeneklerini karşılaştırmalı olarak inceleyebilir, bütçenize "
        "en uygun hizmeti seçebilirsiniz.</p>"
        "<p>{keyword} hakkında {il}'de merak edilen tüm konuları "
        "bu sayfada yanıtlıyoruz. Fiyatlandırma, hizmet kalitesi ve "
        "müşteri yorumları gibi önemli kriterleri tek tek "
        "değerlendirdik.</p>"
        "<p>{il} ili genelinde {keyword} sektörü hızla büyümeye devam "
        "ediyor. Siz de gelişmeleri yakından takip ederek sektördeki "
        "en yeni ve kaliteli hizmetlere ulaşabilirsiniz.</p>"
    ),
    (
        "<p>{il} şehir merkezi ve ilçelerinde <strong>{keyword}</strong> "
        "hizmeti veren kuruluşların güncel listesini sizler için "
        "derledik. {il}'de {keyword} denilince akla gelen ilk "
        "adres burası.</p>"
        "<p>Hizmet kalitesi, müşteri memnuniyeti ve uygun fiyat "
        "politikası ile {il}'de {keyword} sektörünün öncü "
        "isimlerini bir araya getirdik.</p>"
        "<p>{keyword} fiyatları, kampanyaları ve daha "
        "fazlası için web sitemizi ziyaret edebilir, {il} halkına "
        "özel avantajlardan yararlanabilirsiniz.</p>"
    ),
    (
        "<p>{il} bölgesinde {keyword} ihtiyacı olan herkesin "
        "ziyaret etmesi gereken kapsamlı kaynak sayfası. "
        "{il}'de {keyword} konusunda en güncel veriler, "
        "kullanıcı yorumları ve fiyat karşılaştırmaları "
        "bu sayfada buluşuyor.</p>"
        "<p>{keyword} sektöründe {il}'de faaliyet gösteren "
        "öncü firmalar, uzman kadroları ve yenilikçi hizmet "
        "anlayışları ile hizmetinizdedir. Amacımız {il} "
        "sakinlerine en iyi {keyword} deneyimini yaşatmaktır.</p>"
        "<p>Güncel {keyword} fiyatları, kampanyalar ve "
        "sektör haberleri hakkında bilgi almak için web "
        "sitemizi düzenli takip edin. {il}'de {keyword} "
        "hizmeti almak artık çok kolay.</p>"
    ),
    (
        "<p>{il} ve ilçelerinde <strong>{keyword}</strong> "
        "hizmeti sunan kaliteli firmaları bir platformda "
        "topladık. Sitemiz {il} halkının {keyword} "
        "konusunda tüm ihtiyaçlarına cevap verecek şekilde "
        "tasarlanmıştır.</p>"
        "<p>Bilgiye hızlı erişim ilkesiyle hazırladığımız "
        "{keyword} sayfamızda, {il} merkezli tüm hizmet "
        "sağlayıcıların güncel iletişim bilgilerini, "
        "çalışma saatlerini ve sundukları hizmetleri "
        "detaylı olarak bulabilirsiniz.</p>"
        "<p>{il} şehrinde {keyword} sektörü hakkında en "
        "doğru ve güncel bilgi kaynağı olma yolunda "
        "ilerliyoruz. Siz değerli kullanıcılarımız için "
        "{keyword} konusunu her yönüyle ele alıyoruz.</p>"
    ),
    (
        "<p>{il}'de {keyword} arıyorsanız doğru adrestesiniz. "
        "{il} sınırları içinde {keyword} konusunda "
        "uzmanlaşmış işletmelerimiz, size en kaliteli "
        "hizmeti sunmak için hazır bekliyor.</p>"
        "<p>{keyword} hizmeti, {il} genelinde her geçen gün "
        "daha fazla talep görmektedir. Biz de bu talebe "
        "karşılık vermek için {il}'deki en iyi {keyword} "
        "firmalarını araştırdık ve sizler için sıraladık.</p>"
        "<p>Bütçenize ve ihtiyacınıza en uygun {keyword} "
        "seçeneğini bulmak için sayfamızdaki firmaları "
        "inceleyebilir, {il}'deki en iyi hizmete "
        "ulaşabilirsiniz.</p>"
    ),
    (
        "<p>{il} merkezli <strong>{keyword}</strong> hizmeti "
        "konusunda en güncel bilgileri bu sayfada bulacaksınız. "
        "{il}'de {keyword} arayanlar için tüm detayları "
        "tek bir kaynakta topladık.</p>"
        "<p>Müşteri odaklı yaklaşımımız ve kaliteli hizmet "
        "anlayışımızla {il}'de {keyword} denince akla gelen "
        "ilk adres olmayı hedefliyoruz. Her bütçeye uygun "
        "{keyword} alternatiflerini listeledik.</p>"
        "<p>{il} ve çevre ilçelerindeki en güncel {keyword} "
        "bilgileri, fiyat listeleri ve firma iletişim bilgileri "
        "için sayfamızı yer imlerinize ekleyin.</p>"
    ),
]

QUESTION_PATTERNS = [
    "{keyword} {il}",
    "{keyword} {il} fiyatları 2026",
    "{keyword} {il} hizmet fiyatları",
    "{keyword} {il} telefon numarası",
    "{keyword} {il} adres",
    "{keyword} {il} randevu",
    "{keyword} {il} en iyi hizmet",
    "{keyword} {il} ücretleri ne kadar",
    "{keyword} {il} fiyat listesi",
    "{keyword} {il} nerede",
    "{keyword} {il} müşteri yorumları",
    "{keyword} {il} 7/24 acil servis",
    "{keyword} {il} uygun fiyat",
    "{keyword} {il} tavsiye",
    "{keyword} {il} en yakın",
    "{keyword} {il} çalışma saatleri",
    "{keyword} {il} online randevu",
    "{keyword} {il} whatsapp iletişim",
    "{keyword} {il} güvenilir firmalar",
    "{keyword} {il} en ucuz",
    "{keyword} {il} kampanyalar",
    "{keyword} {il} indirim",
    "{keyword} {il} profesyonel destek",
    "{keyword} {il} ücretsiz danışmanlık",
    "{keyword} {il} evde hizmet",
    "{keyword} {il} nasıl gidilir",
    "{keyword} {il} harita konumu",
    "{keyword} {il} iş ilanları",
    "{keyword} {il} kariyer",
    "{keyword} {il} hakkında",
    "{keyword} {il} rehberi",
    "{keyword} {il} karşılaştırma",
    "{keyword} {il} sıkça sorulan sorular",
    "{keyword} {il} kullanıcı deneyimleri",
]


class SpintaxEngine:
    @staticmethod
    def spin_text(text: str, intensity: float = 0.4) -> str:
        words = text.split()
        result = []
        for w in words:
            clean = w.strip(".,;:!?\"'()[]{}<>").lower()
            if clean in SPINTAX_MAP and random.random() < intensity:
                replacement = random.choice(SPINTAX_MAP[clean])
                if w[0].isupper():
                    replacement = replacement.capitalize()
                punct = ""
                if w and w[-1] in ".,;:!?\"'()[]":
                    punct = w[-1]
                result.append(replacement + punct)
            else:
                result.append(w)
        return " ".join(result)

    @staticmethod
    def spin_html_template(template: str, loc_name: str, keyword: str) -> str:
        content = template.replace("{il}", loc_name).replace("{keyword}", keyword)
        content = SpintaxEngine.spin_text(content, intensity=0.35)
        return content

    @staticmethod
    def generate_unique_body(loc: dict, keyword: str) -> str:
        seed = hashlib.md5(f"{loc['slug']}:{keyword}".encode()).hexdigest()
        rng = random.Random(seed)
        tmpl_idx = rng.randint(0, len(BODY_TEMPLATES) - 1)
        template = BODY_TEMPLATES[tmpl_idx]
        body = SpintaxEngine.spin_html_template(template, loc["name"], keyword)
        body = SpintaxEngine.apply_spintax_syntax(body)
        return body

    @staticmethod
    def apply_spintax_syntax(text: str) -> str:
        def _replacer(m: re.Match) -> str:
            options = [o.strip() for o in m.group(1).split("|")]
            return random.choice(options)
        pattern = r"\{([^{}]+?)\}"
        prev = None
        while prev != text:
            prev = text
            text = re.sub(pattern, _replacer, text)
        return text

    @staticmethod
    def generate_title(keyword: str, loc_name: str, pattern: str) -> str:
        return pattern.format(il=loc_name, keyword=keyword)

    @staticmethod
    def generate_meta(keyword: str, loc_name: str) -> str:
        return (
            f"{loc_name} {keyword} | Güncel {keyword} hizmetleri, "
            f"fiyatları ve firmaları {loc_name} sayfamızda. "
            f"2026 yılı en güncel {keyword} bilgileri."
        )
