---
name: Socio-Sanitary Excellence
colors:
  surface: '#f8f9ff'
  surface-dim: '#cbdbf5'
  surface-bright: '#f8f9ff'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#eff4ff'
  surface-container: '#e5eeff'
  surface-container-high: '#dce9ff'
  surface-container-highest: '#d3e4fe'
  on-surface: '#0b1c30'
  on-surface-variant: '#42474e'
  inverse-surface: '#213145'
  inverse-on-surface: '#eaf1ff'
  outline: '#72777f'
  outline-variant: '#c1c7d0'
  surface-tint: '#2e628e'
  primary: '#003354'
  on-primary: '#ffffff'
  primary-container: '#0b4a75'
  on-primary-container: '#89baeb'
  inverse-primary: '#9acbfd'
  secondary: '#006c49'
  on-secondary: '#ffffff'
  secondary-container: '#6cf8bb'
  on-secondary-container: '#00714d'
  tertiary: '#472a00'
  on-tertiary: '#ffffff'
  tertiary-container: '#653e00'
  on-tertiary-container: '#faa213'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#cfe5ff'
  primary-fixed-dim: '#9acbfd'
  on-primary-fixed: '#001d33'
  on-primary-fixed-variant: '#0b4a75'
  secondary-fixed: '#6ffbbe'
  secondary-fixed-dim: '#4edea3'
  on-secondary-fixed: '#002113'
  on-secondary-fixed-variant: '#005236'
  tertiary-fixed: '#ffddb8'
  tertiary-fixed-dim: '#ffb95f'
  on-tertiary-fixed: '#2a1700'
  on-tertiary-fixed-variant: '#653e00'
  background: '#f8f9ff'
  on-background: '#0b1c30'
  surface-variant: '#d3e4fe'
typography:
  headline-xl:
    fontFamily: Public Sans
    fontSize: 40px
    fontWeight: '700'
    lineHeight: 48px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Public Sans
    fontSize: 32px
    fontWeight: '700'
    lineHeight: 40px
    letterSpacing: -0.01em
  headline-md:
    fontFamily: Public Sans
    fontSize: 24px
    fontWeight: '600'
    lineHeight: 32px
  body-lg:
    fontFamily: Public Sans
    fontSize: 18px
    fontWeight: '400'
    lineHeight: 28px
  body-md:
    fontFamily: Public Sans
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
  body-sm:
    fontFamily: Public Sans
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
  label-md:
    fontFamily: Public Sans
    fontSize: 14px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Public Sans
    fontSize: 12px
    fontWeight: '500'
    lineHeight: 14px
    letterSpacing: 0.02em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  base: 8px
  xs: 4px
  sm: 12px
  md: 24px
  lg: 40px
  xl: 64px
  gutter: 24px
  margin: 32px
---

## Brand & Style

The design system is anchored in the principles of reliability, clarity, and human-centric efficiency. Designed for a socio-sanitary context, the visual language must bridge the gap between rigorous medical professionalism and the empathetic nature of social care. The target audience includes healthcare practitioners, social workers, and facility administrators who require a tool that feels secure and minimizes cognitive load during high-stakes decision-making.

The chosen style is **Corporate / Modern**. It leverages a structured layout, a balanced color palette, and purposeful whitespace to evoke a sense of calm and institutional stability. By avoiding trend-heavy aesthetics like glassmorphism or brutalism, this design system ensures long-term usability and universal accessibility, prioritizing functional excellence over decorative flair.

## Colors

The color strategy for this design system is built on "Trustworthy Blues" and "Healthcare Greens." The **Primary Blue** (#0B4A75) is deep and authoritative, used for navigation and primary actions to establish confidence. The **Secondary Green** (#10B981) represents health, vitality, and successful "matching" outcomes, used primarily for positive status indicators and success states.

A palette of **Soft Grays** is utilized for backgrounds to reduce screen glare and create a clear distinction between the canvas and interactive surfaces. **Clean Whites** are reserved for cards and content containers, ensuring that information stands out. Tertiary accents in amber are used sparingly for warnings or "pending" states, maintaining a professional hierarchy of urgency.

## Typography

The design system utilizes **Public Sans** for all levels of the hierarchy. As an institutional sans-serif, it offers exceptional legibility in both digital and printed contexts—a critical requirement for healthcare documentation and matching profiles. 

The type scale is generous, emphasizing a strong vertical rhythm. Headlines use a heavier weight and tighter letter-spacing to appear grounded, while body text maintains a neutral 1.5x line height to ensure readability for users of all ages and visual abilities. Label styles are distinctively weighted to help users differentiate between "data headers" and "user input" at a glance.

## Layout & Spacing

The design system employs a **Fixed Grid** approach for desktop dashboards to ensure that information-dense data remains organized and predictable. A 12-column system is used with 24px gutters, allowing for modular layouts where job postings and profiles can occupy 4, 6, or 8-column spans depending on the required detail level.

The spacing rhythm follows an 8px base unit, ensuring consistent vertical and horizontal alignment. Generous margins (32px+) are used around major layout containers to prevent the UI from feeling cluttered, reinforcing the "efficient and clean" brand personality.

## Elevation & Depth

To maintain a secure and professional feel, this design system uses **Tonal Layers** combined with **Low-contrast Outlines**. Depth is communicated through subtle shifts in background color (e.g., a white card on a light gray background) rather than aggressive shadows.

Where elevation is required for interactivity (such as a hovered job card or a modal), a single "Ambient Shadow" style is used: a very soft, diffused drop shadow with a low opacity (8-10%) and a slight blue tint (#0B4A75) to keep the depth feeling integrated with the primary brand color. This avoids the "floating" sensation of standard shadows and keeps the interface feeling grounded.

## Shapes

The shape language of the design system is defined as **Soft**. Standard components like input fields, buttons, and card containers utilize a 4px (0.25rem) corner radius. Large containers and dashboard modules use an 8px (0.5rem) radius.

This subtle rounding balances the precision of a professional medical tool with the approachability of a social platform. It avoids the clinical coldness of sharp 0px corners while maintaining a more serious tone than highly rounded or pill-shaped "playful" interfaces.

## Components

### Cards
Job postings and worker profiles are housed in clean white cards. These use a 1px border (#E2E8F0) and minimal shadows. Information is structured using a "header-body-footer" format: the header contains the primary title and status badge, the body contains key metadata (location, hours, rate), and the footer contains the primary CTA.

### Forms
Profile building is facilitated through structured, single-column forms to minimize cognitive fatigue. Input fields use a subtle gray background (#F1F5F9) when empty, turning white with a 2px primary blue border upon focus. Labels are always persistent and placed above the input field.

### Status Chips
A critical component for this system is the status chip. These are semi-pill-shaped with low-saturation background colors and high-contrast text (e.g., a light green background with dark green text for "Verified").

### Dashboards
Dashboards utilize a "Side Navigation" pattern to keep the main workspace clear. Key performance indicators (KPIs) like "Active Matches" or "Open Shift Requests" are displayed at the top in simplified "Stat Cards" to provide immediate situational awareness.