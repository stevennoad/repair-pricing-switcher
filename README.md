# Repair Pricing Switcher

An Elementor widget that provides **Device → Model** dropdowns and renders a dynamic repair pricing table inside a single Elementor Template.

---

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Elementor

---

## Install

1. Download the latest ZIP from releases.
2. **Plugins → Add New → Upload Plugin**
3. Install & activate **Repair Pricing Switcher**.

---

## Setup

### 1) Create the pricing template
1. **Templates → Saved Templates** → create a new template.
2. Add a **Shortcode** widget with:

```text
[rps_prices]
```

Style this template however you want (card, background, headings, etc).

### 2) Add the widget
1. Edit your page with Elementor.
2. Drag **Repair Pricing Switcher** onto the page.
3. In **Panel Template**, choose the template you created.

---

## Add pricing data

In the widget **Content** section, add rows under **Device → Model**.
Each row is one model and its pricing table.

**Pricing Rows format (one per line):**

```text
Service | AppleCare+ | Price
```

Example:

```text
Battery service | - | £109
Back glass damage | £25 | £145
Rear camera damage | - | £269
Screen damage | £25 | £389
Other damage | £50 | £795
```

---

## Notes

- “Default Device/Model” uses **exact matching**.
- If AppleCare+ values are blank, you can hide the AppleCare+ column.
