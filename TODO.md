# Cemetery Project Tasks - AA Phase 3 Dropdown & Public Map Completion

## Current Task Progress
✅ **Plan approved by user**

## Remaining Steps (to be marked done progressively)

### 1. ✅ **Generate Missing DB Plots for AA Block Phase 3**
   - Edited `database_cleaned.sql`: Added 40 new plots (AA sec2/3, IDs 1154-1193).
   - **User: Import SQL, reply "DB imported"**

### 2. **Fix Public Map DOB Display**
   - Edit `api/get_public_lot_details.php`: Add `d.birth_date` to SELECT.
   - Update public_cemetery_map.php modal display DOB.

### 3. ✅ **Add AA Section Dropdown to Public Map**
   - `public_cemetery_map.php`: AA dropdown + DOB complete.

### 4. **Add AA Section Dropdown to Staff Map**
   - Edit `cemetery_map.php`: Add sidebar dropdown, update render for AA sections.

### 5. **Testing & Completion**
   - Test both maps: Dropdown switches sections correctly.
   - Verify public map shows name/DOB/DOD only.
   - Run `attempt_completion`.

**Next Action:** Step 1 - Edit database_cleaned.sql

