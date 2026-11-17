# Mockup Reference - NutriCare UI

## Overview
Dokumentasi ini berisi referensi visual untuk implementasi UI NutriCare berdasarkan mockup Figma.

## Mockup Tersedia
- **Logistik PMT - Stok Gudang**: Halaman utama untuk manajemen stok gudang PMT

## Token Warna (Hasil Sampling)

### Primary (Teal/Hijau)
- `primary-600`: #0d9488 - Tombol utama, menu aktif, aksen
- `primary-50`: #f0fdfa - Background menu aktif (muda)
- `primary-700`: #0f766e - Hover states

### Neutral/Gray
- `neutral-50`: #f8fafc - Background halaman
- `neutral-100`: #f1f5f9 - Background input
- `neutral-200`: #e2e8f0 - Border
- `neutral-500`: #64748b - Text secondary
- `neutral-800`: #1e293b - Text primary

### Status Colors

#### Success (Green)
- Background: #dcfce7 (success-100)
- Text: #166534 (success-800)
- Border: #16a34a (success-600)

#### Warning (Orange)
- Background: #fef3c7 (warning-100)
- Text: #92400e (warning-800)
- Border: #d97706 (warning-600)

#### Info (Blue)
- Background: #dbeafe (info-100)
- Text: #1e40af (info-800)
- Border: #2563eb (info-600)

#### Danger (Red)
- Background: #fee2e2 (danger-100)
- Text: #991b1b (danger-800)
- Border: #dc2626 (danger-600)

## Typography

### Font Family
- **Primary**: Inter Variable (semibold untuk headings, medium untuk body)
- **Fallback**: system-ui, sans-serif

### Font Sizes
- **H1 (Page Title)**: 1.5rem (24px) - font-bold
- **H2 (Section Title)**: 1.25rem (20px) - font-semibold
- **Body**: 0.875rem (14px) - font-medium
- **Small/Labels**: 0.75rem (12px) - font-medium
- **Tiny (Uppercase Labels)**: 0.6875rem (11px) - font-bold uppercase tracking-wider

## Spacing & Layout

### Sidebar
- Width: 240px (w-60)
- Background: white
- Border right: 1px solid neutral-200

### Topbar
- Height: 64px (h-16)
- Background: white
- Border bottom: 1px solid neutral-200

### Content Area
- Padding: 2rem (p-8)
- Max width: 1280px (max-w-7xl)
- Background: neutral-50

### Cards
- Border radius: 0.75rem (rounded-xl)
- Border: 1px solid neutral-200
- Background: white
- Padding: 1.5rem (p-6)

## Component Patterns

### KPI Cards
1. **Normal Card**
   - Border: neutral-200
   - Icon background: neutral-100
   - Icon color: neutral-600

2. **Warning Card**
   - Border: warning-200
   - Icon background: warning-100
   - Icon color: warning-600
   - Text: warning-800

3. **Success Card**
   - Border: success-200
   - Icon background: success-100
   - Icon color: success-600

### Status Pills
- Border radius: full (rounded-full)
- Padding: 0.25rem 0.75rem (px-3 py-1)
- Font size: 0.75rem (text-xs)
- Font weight: semibold
- Display: inline-flex items-center gap-1.5
- Dot indicator: w-1.5 h-1.5 rounded-full

#### Variants
1. **Prioritas FEFO** (Warning)
   - Background: warning-100
   - Text: warning-800
   - Dot: warning-600

2. **Antrean Berikutnya** (Info)
   - Background: info-100
   - Text: info-800
   - Dot: info-600

3. **Stok Aman** (Success)
   - Background: success-100
   - Text: success-800
   - Dot: success-600

### Buttons

#### Primary Button
- Background: primary-600
- Text: white
- Hover: primary-700
- Padding: 0.5rem 1rem (px-4 py-2)
- Border radius: 0.5rem (rounded-lg)
- Font weight: semibold
- Font size: 0.875rem (text-sm)

#### Outline Button
- Background: transparent
- Border: 2px solid neutral-200
- Text: neutral-700
- Hover background: neutral-50
- Same padding as primary

#### Outline Primary Button (Teal)
- Border: 2px solid primary-600
- Text: primary-700
- Hover background: primary-50

### Table
- Header background: neutral-100
- Header text: 0.6875rem uppercase tracking-wider font-bold neutral-600
- Cell padding: 1rem (p-4)
- Row border: 1px solid neutral-200
- Hover: neutral-50
- Highlight row (priority): warning-50

### Form Inputs
- Border: 1px solid neutral-200
- Background: neutral-50
- Focus: ring-2 ring-primary-500
- Border radius: 0.5rem (rounded-lg)
- Padding: 0.5rem 0.75rem (px-3 py-2)
- Font size: 0.875rem (text-sm)

## Responsive Breakpoints
- Mobile: 360px - 639px
- Tablet: 640px - 1023px
- Desktop: 1024px+
- Wide: 1440px+
- Max: 1920px

### Responsive Behavior
1. **< 640px (Mobile)**
   - Sidebar: Hidden, show hamburger menu
   - Search: Icon only (opens modal)
   - User name: Hidden (avatar only)
   - Table: Horizontal scroll with indicator

2. **640px - 1023px (Tablet)**
   - Sidebar: Collapsible drawer
   - Search: Full width
   - Buttons: May stack vertically

3. **>= 1024px (Desktop)**
   - Sidebar: Always visible (sticky)
   - Full layout as mockup
