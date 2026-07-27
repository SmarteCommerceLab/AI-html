"""Build the lightweight Font Awesome fonts used by the public theme."""

from pathlib import Path
import re

from fontTools import subset
from fontTools.ttLib import TTFont


ROOT = Path(__file__).resolve().parents[1]
CSS = ROOT / "resource" / "css" / "fontawesome"
FONTS = ROOT / "resource" / "css" / "webfonts"

SOLID_ICONS = {
    "address-book", "arrow-down", "arrow-right", "bars", "book", "book-open",
    "boxes-stacked", "briefcase", "broom", "building", "bullhorn",
    "cart-shopping", "chart-line", "chevron-down", "chevron-left",
    "chevron-right", "circle-check", "clock", "code", "cube", "cubes",
    "database", "envelope", "folder-open", "graduation-cap", "headset",
    "house", "industry", "info-circle", "key", "layer-group", "location-dot",
    "newspaper", "palette", "paper-plane", "pen-nib", "phone", "robot",
    "rotate-left", "search", "share-nodes", "shoe-prints", "signal", "sliders",
    "triangle-exclamation", "trophy", "user", "users", "video",
}

BRAND_ICONS = {
    "facebook-f", "instagram", "linkedin-in", "twitter", "whatsapp", "youtube",
}


def icon_map(css_path: Path) -> dict[str, int]:
    css = css_path.read_text(encoding="utf-8")
    mapping: dict[str, int] = {}
    pattern = re.compile(r"([^{}]+):before\{content:\"\\([0-9a-f]+)\"\}")
    for selectors, codepoint in pattern.findall(css):
        for name in re.findall(r"\.fa-([a-z0-9-]+)", selectors):
            mapping[name] = int(codepoint, 16)
    return mapping


def build_font(source: str, target: str, names: set[str], mapping: dict[str, int]) -> None:
    missing = sorted(names.difference(mapping))
    if missing:
        raise RuntimeError(f"Missing icon mappings: {', '.join(missing)}")

    options = subset.Options()
    options.flavor = "woff2"
    options.with_zopfli = False
    font = TTFont(FONTS / source)
    subsetter = subset.Subsetter(options=options)
    subsetter.populate(unicodes=[mapping[name] for name in sorted(names)])
    subsetter.subset(font)
    font.flavor = "woff2"
    font.save(FONTS / target)


def build_brand_css(mapping: dict[str, int]) -> None:
    rules = "".join(
        f'.fa-{name}:before{{content:"\\{mapping[name]:x}"}}'
        for name in sorted(BRAND_ICONS)
    )
    css = (
        '/*! Font Awesome Free 6.4.2 core brand subset */'
        '@font-face{font-family:"Font Awesome 6 Brands";font-style:normal;'
        'font-weight:400;font-display:swap;'
        'src:url(../webfonts/aihl-brands-core.woff2) format("woff2")}'
        '.fa-brands,.fab{font-weight:400}'
        + rules
    )
    (CSS / "aihl-brands-core.min.css").write_text(css, encoding="utf-8")


def main() -> None:
    for css_name, ttf_name in (
        ("solid.min.css", "fa-solid-900.ttf"),
        ("brands.min.css", "fa-brands-400.ttf"),
    ):
        css_path = CSS / css_name
        css = css_path.read_text(encoding="utf-8")
        css = css.replace(
            f',url(../webfonts/{ttf_name}) format("truetype")',
            "",
        ).replace("font-display:block", "font-display:swap")
        css_path.write_text(css, encoding="utf-8")

    (CSS / "regular.min.css").write_text(
        '/*! Font Awesome Free 6.4.2 regular font */'
        '@font-face{font-family:"Font Awesome 6 Free";font-style:normal;'
        'font-weight:400;font-display:swap;'
        'src:url(../webfonts/fa-regular-400.woff2) format("woff2")}'
        '.fa-regular,.far{font-weight:400}',
        encoding="utf-8",
    )

    solid = icon_map(CSS / "fontawesome.min.css")
    brands = icon_map(CSS / "brands.min.css")
    build_font("fa-solid-900.woff2", "aihl-solid-core.woff2", SOLID_ICONS, solid)
    build_font("fa-brands-400.woff2", "aihl-brands-core.woff2", BRAND_ICONS, brands)
    build_brand_css(brands)
    (CSS / "aihl-solid-core.min.css").write_text(
        '/*! Font Awesome Free 6.4.2 core solid subset */'
        '@font-face{font-family:"Font Awesome 6 Free";font-style:normal;'
        'font-weight:900;font-display:swap;'
        'src:url(../webfonts/aihl-solid-core.woff2) format("woff2")}'
        '.fa-solid,.fas{font-weight:900}',
        encoding="utf-8",
    )


if __name__ == "__main__":
    main()
