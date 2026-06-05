---
name: Titik Nol Heritage
colors:
  surface: '#faf9f6'
  surface-dim: '#dadad7'
  surface-bright: '#faf9f6'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f4f4f0'
  surface-container: '#eeeeea'
  surface-container-high: '#e8e8e5'
  surface-container-highest: '#e3e3df'
  on-surface: '#1a1c1a'
  on-surface-variant: '#424843'
  inverse-surface: '#2f312f'
  inverse-on-surface: '#f1f1ed'
  outline: '#727972'
  outline-variant: '#c2c8c0'
  surface-tint: '#466550'
  primary: '#163422'
  on-primary: '#ffffff'
  primary-container: '#2d4b37'
  on-primary-container: '#99baa1'
  inverse-primary: '#adcfb4'
  secondary: '#4a654e'
  on-secondary: '#ffffff'
  secondary-container: '#c9e8cb'
  on-secondary-container: '#4e6952'
  tertiary: '#472429'
  on-tertiary: '#ffffff'
  tertiary-container: '#613a3f'
  on-tertiary-container: '#daa5ab'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c8ebd0'
  primary-fixed-dim: '#adcfb4'
  on-primary-fixed: '#022110'
  on-primary-fixed-variant: '#2f4d39'
  secondary-fixed: '#cceace'
  secondary-fixed-dim: '#b0ceb2'
  on-secondary-fixed: '#07200f'
  on-secondary-fixed-variant: '#334d38'
  tertiary-fixed: '#ffd9dc'
  tertiary-fixed-dim: '#efb9be'
  on-tertiary-fixed: '#311217'
  on-tertiary-fixed-variant: '#633c41'
  background: '#faf9f6'
  on-background: '#1a1c1a'
  surface-variant: '#e3e3df'
  anniversary-gold: '#C5A059'
  topo-gray: '#F4F5F2'
  memorial-slate: '#4A5568'
  status-success: '#48BB78'
  status-pending: '#ECC94B'
  status-error: '#F56565'
typography:
  display-hero:
    fontFamily: Plus Jakarta Sans
    fontSize: 48px
    fontWeight: '800'
    lineHeight: '1.1'
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: '1.2'
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: '1.3'
  body-lg:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '400'
    lineHeight: '1.6'
  body-md:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: '1.6'
  label-caps:
    fontFamily: JetBrains Mono
    fontSize: 12px
    fontWeight: '500'
    lineHeight: '1.0'
    letterSpacing: 0.1em
  headline-lg-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '700'
    lineHeight: '1.2'
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  container-max: 1200px
  gutter: 1.5rem
  margin-mobile: 1rem
  section-gap: 4rem
  stack-sm: 0.5rem
  stack-md: 1rem
---

## Brand & Style

This design system is crafted for the "Kembali ke Titik Nol" Geodesi 96 Reunion, balancing the precision of geodetic engineering with the warmth of a 30-year legacy. The brand personality is **Nostalgic, Professional, and Communal**. It aims to evoke a sense of "coming home" (Pulang) while respecting the technical background of the alumni.

The visual style is a **Modern-Corporate hybrid with Tactile elements**. It utilizes clean, systematic layouts characteristic of modern SaaS, but softens them with organic topographic textures and "sticker-style" decorative accents that reference physical mementos. The interface should feel structured like a map but readable like a personal letter.

**Key Visual Pillars:**
- **Precision:** Use of crosshairs, coordinates, and hairline dividers.
- **Legacy:** Earthy greens and subtle gold accents to mark the 30th anniversary.
- **Organic Depth:** Topographic background patterns that add texture without compromising text legibility.

## Colors

The palette is rooted in the natural tones of surveying and land management. 

- **Primary (Deep Forest Green):** Used for primary actions, navigation backgrounds, and authoritative typography. It represents the "grounding" of the reunion.
- **Secondary (Sage Green):** Used for subtle backgrounds, secondary buttons, and decorative elements. It provides a soft, approachable contrast to the deep primary green.
- **Accent (Anniversary Gold):** Reserved exclusively for 30th-anniversary callouts, special highlights, and "premium" UI touches like high-tier donor badges.
- **Neutral (Topo-Gray/Off-White):** A warm, light gray base that prevents the "coldness" of pure white.

**Functional Colors:**
- **Memorial:** Use `memorial-slate` for alumni who have passed away, creating a respectful, somber distinction from the vibrant brand greens.
- **Status:** Standard semantic colors are muted to match the earthy palette (e.g., a "Sage-Red" for errors).

## Typography

The typography strategy emphasizes clarity and technical heritage.

- **Headlines:** **Plus Jakarta Sans** provides a modern, friendly, and geometric feel that works exceptionally well for large titles and the reunion theme.
- **Body:** **Inter** is used for its unmatched legibility across devices, essential for long-form alumni stories and itinerary details.
- **Technical Accents:** **JetBrains Mono** is used for metadata, coordinates, timestamps, and labels. This monospaced choice is a nod to the geodetic data and technical logs familiar to the class of '96.

**Scale:**
On mobile, the `display-hero` and `headline-lg` levels should be reduced by 15-20% to maintain a clean layout without excessive word-breaking.

## Layout & Spacing

The design system employs a **12-column fixed grid** for desktop to ensure content remains centered and readable on large monitors. For mobile, it transitions to a single-column fluid layout with generous margins.

- **Whitespace:** Use whitespace aggressively to separate different "Eras" of content (e.g., Past vs. Present). 
- **Rhythm:** An 8px base scaling system is used for all internal component spacing (padding, gaps).
- **Safe Zones:** Background topographic patterns should never intersect with high-density text areas; use translucent "Sage Green" or "Off-White" overlays to maintain contrast.

## Elevation & Depth

This system avoids heavy drop shadows in favor of **Tonal Layering** and **Ghost Outlines**.

- **Surfaces:** Use subtle shifts in background color (e.g., moving from `#F4F5F2` to `#FFFFFF`) to indicate hierarchy.
- **Borders:** Use thin (1px) borders in `secondary-color` at 30% opacity to define cards.
- **Elevation for Interaction:** Only apply a soft, diffused shadow (`0 10px 15px -3px rgba(45, 75, 55, 0.1)`) when a card is hovered, creating a subtle lift effect.
- **Glassmorphism:** Use for fixed navigation bars at the top of the screen—a light sage-tinted backdrop blur (`blur(10px)`) to maintain context of the topographic pattern underneath while scrolling.

## Shapes

The shape language reflects the "calibration" theme—precise but friendly.

- **Primary Radius:** A 0.5rem (8px) radius is the standard for cards and input fields.
- **Pill Shapes:** Used exclusively for status badges (RSVP, Payment) and the "30 Taon" anniversary tag.
- **Decorative Shapes:** Use the "Sticker" aesthetic for iconography—these should appear as if they are placed *on top* of the UI, occasionally breaking the grid for a nostalgic, scrapbook feel.
- **Avatars:** Current photos use standard circles, while "College Era" photos use a "Postage Stamp" scalloped edge or a high-contrast square border to distinguish the timeline visually.

## Components

### Buttons
- **Primary:** Deep Forest Green background, White text, 8px radius. High contrast.
- **Secondary:** Sage Green background (20% opacity), Deep Forest Green text.
- **Tertiary/Ghost:** No background, JetBrains Mono label with a leading crosshair icon.

### Cards
- **Alumni Profile Card:** White background, 1px Sage border. Features a dual-photo layout (Past/Present). Metadata is displayed in JetBrains Mono.
- **Memorial Card:** Slate-gray border, desaturated imagery, and a specific "In Memoriam" ribbon in the top-right corner.

### Input Fields
- Clean, 1px border. Focus state uses a 2px Deep Forest Green border. Labels should be small, uppercase JetBrains Mono for a "data entry" feel.

### Chips & Badges
- **Status Badges:** Use the pill shape. `Hadir` (Attending) uses a light green tint; `Belum Bayar` (Unpaid) uses a light gold/yellow tint.

### Decorative Elements
- **Topographic Backgrounds:** SVG patterns used in section backgrounds at 5-10% opacity.
- **Crosshairs:** Used as decorative markers in the corners of sections or as bullet points for lists to reinforce the Geodesy theme.