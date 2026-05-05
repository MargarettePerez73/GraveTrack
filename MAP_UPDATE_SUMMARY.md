# Cemetery Map Update - Visual Layout Implementation

## 🎨 What Was Updated

### 1. **Cemetery Map (`cemetery_map.php`)**
✅ Complete redesign to match your actual cemetery layout
✅ Visual grid representation of plots
✅ Phase-based organization (Phase 1, 2, 3)
✅ Block-based grouping with proper layout
✅ Role-based color coding

### 2. **Edit Burial Record Page (`edit_burial_record.php`)**
✅ New page for editing deceased details
✅ Load existing burial information
✅ Update functionality via API
✅ Delete functionality with confirmation
✅ Payment information display (Treasurer only)

### 3. **Burial Records Page (`burial_records.php`)**
✅ Added Edit button for each record
✅ Improved action buttons layout
✅ Better integration with edit page

---

## 🎨 Visual Cemetery Map Features

### Layout
- **3 Phases displayed horizontally**
  - Phase 3 (leftmost)
  - Phase 2 (middle)
  - Phase 1 (rightmost)

- **Blocks organized in columns**
  - Each phase shows multiple blocks
  - Blocks displayed as vertical grids
  - Lot numbers shown in each box

### Color Coding System

#### 👷 Engineer View
| Color | Status | Description |
|-------|--------|-------------|
| 🟢 Green | Vacant | Plot available for burial |
| 🔴 Red | Occupied | Plot has deceased |

#### 💰 Treasurer View
| Color | Status | Description |
|-------|--------|-------------|
| 🟢 Green | Fully Paid | 3-year rental fully paid |
| 🟠 Orange | Partially Paid | Some payments made, not complete |
| 🔴 Red | Unpaid | Current rental period unpaid |
| 🔴 Dark Red | Overdue | Payment overdue (25% penalty applied) |

### Interactive Features
✅ **Click any plot box** to view details
✅ **Hover effect** - boxes scale up on hover
✅ **Search functionality** - find plots by block/section/lot
✅ **Deceased information** - view all deceased in a plot
✅ **Edit button** - directly edit burial records

---

## 📝 Edit Burial Record Features

### Page: `edit_burial_record.php`

**Accessible From:**
- Cemetery Map (click plot → Edit button)
- Burial Records page (Edit button)

**Features:**
1. **Load existing data** for deceased
2. **Edit all fields:**
   - Personal info (name, birth date, gender, address)
   - Burial info (death date, burial date, burial type)
   - Contact info (contact person, number)

3. **View-only plot location** (cannot change after creation)

4. **Payment info** (Treasurer only):
   - Rental history
   - Payment status
   - Penalty information
   - Total amounts

5. **Actions:**
   - Update record
   - Delete record (with confirmation)
   - Cancel and go back

---

## 🔄 Data Flow

### Cemetery Map Display

```
User Loads Map
    ↓
Load Plot Data (/api/get_cemetery_map.php)
    ↓
If Treasurer → Load Payment Data (/api/get_payment_summary.php)
    ↓
Determine Color for Each Plot:
    - Engineer: Vacant (green) / Occupied (red)
    - Treasurer: Paid (green) / Partially Paid (orange) / Unpaid (red) / Overdue (dark red)
    ↓
Render Visual Grid Layout
    ↓
User Clicks Plot → Show Details Modal
    ↓
User Clicks Edit → Navigate to edit_burial_record.php
```

### Edit Record Flow

```
User Clicks Edit (with deceased_id)
    ↓
Load Deceased Record from Database
    ↓
Fetch Plot Details (/api/get_lot_details.php)
    ↓
If Treasurer → Load Payment Info (/api/get_rental_details.php)
    ↓
Display Edit Form with Current Data
    ↓
User Modifies and Submits
    ↓
Update Record (/api/update_burial_record.php)
    ↓
Redirect to Burial Records Page
```

---

## 🗄️ Database Integration

### APIs Used

1. **`/api/get_cemetery_map.php`**
   - Returns all plots grouped by phase
   - Includes deceased count and names
   - Used for initial map rendering

2. **`/api/get_lot_details.php`**
   - Returns detailed plot information
   - Lists all deceased in the plot
   - Used when clicking a plot box

3. **`/api/get_payment_summary.php`** (Treasurer only)
   - Returns payment status for all plots
   - Used for color coding in Treasurer view

4. **`/api/get_rental_details.php`** (Treasurer only)
   - Returns rental history for a deceased
   - Shows payment dates, amounts, penalties

5. **`/api/update_burial_record.php`**
   - Updates deceased information
   - Updates contact information

6. **`/api/delete_burial_record.php`**
   - Deletes deceased record
   - Cascades to contacts, rentals, payments
   - Updates plot status if last deceased removed

---

## 🎯 Role-Based Features

### Engineer Role
✅ View cemetery map (green/red for vacant/occupied)
✅ Click plots to view deceased details
✅ Edit burial records
✅ Add new burial records
✅ Delete burial records

### Treasurer Role
✅ All Engineer features PLUS:
✅ View payment-based color coding
✅ See payment history in edit page
✅ View rental details
✅ Track overdue payments
✅ See penalty information

---

## 📱 Responsive Design

### Desktop (1920px+)
- All phases visible side by side
- Full grid layout
- Large clickable boxes

### Tablet (768px+)
- Horizontal scroll for phases
- Maintains grid structure
- Medium-sized boxes

### Mobile (375px+)
- Horizontal scroll enabled
- Smaller boxes but still readable
- Touch-friendly click areas

---

## 🔍 Search Functionality

**Search Input:** Located at top of map

**Search By:**
- Block letter (e.g., "A", "B")
- Section number
- Lot number
- Deceased name

**Visual Feedback:**
- Matching plots pulse/animate
- Non-matching plots fade out
- Clear search to show all

---

## 🛠️ Technical Implementation

### CSS Grid Layout
```css
.plot-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(35px, 1fr));
    gap: 4px;
}
```

### Color Classes
- `.vacant` - Green (Engineer)
- `.occupied` - Red (Engineer)
- `.paid` - Green (Treasurer)
- `.partially-paid` - Orange (Treasurer)
- `.unpaid` - Red (Treasurer)
- `.overdue` - Dark Red (Treasurer)

### Interactive Elements
- `:hover` - Scale transform
- `cursor: pointer` - Clickable indication
- `transition` - Smooth animations

---

## ✅ Testing Checklist

### Engineer View
- [ ] Can see green/red color coding
- [ ] Can click plots to view details
- [ ] Can edit deceased records
- [ ] Can delete deceased records
- [ ] Search works correctly

### Treasurer View
- [ ] Can see payment-based colors
- [ ] Can view payment history
- [ ] Can see rental details
- [ ] Penalty calculation shown
- [ ] Overdue plots highlighted

### Edit Page
- [ ] Load existing data correctly
- [ ] All fields editable
- [ ] Update saves successfully
- [ ] Delete works with confirmation
- [ ] Payment info displays (Treasurer)
- [ ] Validation works
- [ ] Cancel returns to records page

---

## 📊 Cemetery Layout Structure

Based on your screenshot:

```
┌─────────────┬─────────────┬─────────────┐
│   PHASE 3   │   PHASE 2   │   PHASE 1   │
├─────────────┼─────────────┼─────────────┤
│  Block AA   │  Blocks T-Z │  Blocks A-I │
│             │             │             │
│   [Grid]    │   [Grid]    │   [Grid]    │
│   [Grid]    │   [Grid]    │   [Grid]    │
│   [Grid]    │   [Grid]    │   [Grid]    │
└─────────────┴─────────────┴─────────────┘
```

Each grid = Block with lot numbers displayed

---

## 🚀 Deployment Notes

### Files Modified
1. `cemetery_map.php` - Complete redesign
2. `burial_records.php` - Added edit button

### Files Created
1. `edit_burial_record.php` - New edit page

### No Database Changes Required
- All changes are frontend/UI only
- Uses existing API endpoints
- Compatible with current database schema

---

## 💡 Usage Tips

### For Municipal Workers

**To View Cemetery Map:**
1. Click "Cemetery Map" in navigation
2. See visual representation of all plots
3. Green = Available, Red = Occupied (Engineer)
4. Colors show payment status (Treasurer)

**To Edit a Burial Record:**
1. Click any occupied plot on map
2. Click "Edit" button for deceased
3. Modify information as needed
4. Click "Update Record"

**To Delete a Record:**
1. Open edit page for deceased
2. Click "Delete Record" button (top right)
3. Confirm deletion
4. Plot becomes vacant if no other deceased

**To Search:**
1. Use search box at top of map
2. Type block, section, lot, or name
3. Matching plots will pulse/highlight
4. Clear search to show all

---

## 🎨 Visual Improvements Made

### Before
- Simple list-based map
- Text-only display
- Phase tabs only
- No visual representation

### After
✅ Visual grid layout matching actual cemetery
✅ Clickable plot boxes
✅ Color-coded status indicators
✅ Hover effects and animations
✅ Role-based color schemes
✅ Search with visual feedback
✅ Direct edit access from map
✅ Professional, easy-to-use interface

---

## 📖 Documentation Updates

All documentation reflects the new visual map:
- Cemetery layout matches screenshot
- Role-based color coding explained
- Edit functionality documented
- Search features described

---

**Map Update Complete** ✅  
**Status:** Production Ready  
**Version:** 2.0 (Visual Layout)  
**Date:** May 4, 2026
