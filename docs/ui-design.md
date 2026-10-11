# UI Design Specification

## 1. Sumber Desain

Desain visual dibuat menggunakan Stitch AI.

Sumber desain:
- Link atau lokasi file desain: ...
- Tanggal versi desain: ...
- Status: Draft / Approved

## 2. Halaman yang Tersedia

| Halaman | Role | Status |
|---|---|---|
| Login | Semua user | Ready |
| Dashboard | Semua role | Ready |
| Daftar Reimbursement | Employee, Manager, Finance | Ready |
| Detail Reimbursement | Employee, Manager, Finance, Director | Ready |
| Form Reimbursement | Employee | Ready |
| Approval | Manager, Finance, Director | Ready |
| Transaksi | Finance | Ready |
| Laporan | Finance, Director | Ready |

## 3. Design Tokens

### Warna

- Primary: ...
- Secondary: ...
- Success: ...
- Warning: ...
- Danger: ...
- Background: ...
- Text primary: ...
- Text secondary: ...

### Typography

- Font family: ...
- Heading 1: ...
- Heading 2: ...
- Body: ...
- Caption: ...

### Spacing

Gunakan sistem spacing yang konsisten sesuai desain Stitch.

## 4. Komponen Utama

- Button
- Input
- Select
- Date picker
- Currency input
- Modal
- Table
- Badge status
- Alert
- Toast
- File upload
- Pagination
- Sidebar
- Top navigation
- Summary card
- Chart card

## 5. Aturan Responsiveness

- Desktop: ...
- Tablet: ...
- Mobile: ...

## 6. State UI

Setiap halaman wajib memiliki:

- Loading state.
- Empty state.
- Error state.
- Success state.
- Permission denied state.

## 7. Catatan Implementasi

Desain visual harus mengikuti Stitch, tetapi business logic, permission, validasi, dan data tetap mengikuti:

- `PRD.md`
- `DESIGN.md`
- `docs/approval-flow.md`
- `docs/financial-rules.md`
