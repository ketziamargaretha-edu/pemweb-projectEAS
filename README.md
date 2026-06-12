# Sistem Manajemen Tekstil

## Deskripsi
Sistem manajemen tekstil berbasis web dengan fitur e-commerce dan dashboard statistik untuk industri tekstil.

## Anggota Kelompok & Peran
Kelas 2B 
1. Monix Denar Adistiyono (2043251011)
   - Visual & tata letak dashboard.php, desain tabel produk, ikon interaktif, standardisasi warna, CSS, Bootstrap
2. Ketzia Gracie Margaretha (2043251013) - Ketua
   - beli.php, riwayat.php, pesanan_masuk.php, tracking pengiriman, edit produk supplier, integrasi ke database, statistik per provinsi, upload gambar, color picker,
3. M Refaro Hafid (2043251037)
   - login.php, register.php, auth.php, role management, dashboard dinamis, filter produk, statistik per provinsi, validasi input
4. Nafisha Zalfa (2043251053)
   - tambah.php, edit.php, hapus.php,  manajemen stok

## Fitur Utama

### 1. Dual Role System
- Registrasi akun sebagai **Supplier** atau **Pembeli**
- Login dengan validasi username & password
- Role-based access (tampilan berbeda untuk Supplier & Pembeli)
- Session management & Logout

### 2. Manajemen Produk (Supplier)
- **Tambah Produk** - Input nama, jenis kain, warna, ukuran, stok, harga, provinsi
- **Upload Gambar** - Upload foto produk (JPG, PNG, GIF)
- **Color Picker** - Pilih warna dari palette, deteksi warna otomatis (Merah, Biru, Hijau, Emas, dll)
- **Validasi Warna** - Hanya huruf & spasi (tidak bisa angka/simbol)
- **Edit Produk** - Ubah data produk & ganti gambar
- **Hapus Produk** - Hapus produk & file gambar (dengan pengecekan pesanan aktif)
- **Filter Produk** - Filter warna, jenis kain, supplier, harga, provinsi
- **Sorting Produk** - Urutkan berdasarkan ID, nama, harga, stok, warna, jenis kain

### 3. Pembelian Produk (Pembeli)
- Lihat daftar produk dengan gambar & harga
- Cari produk berdasarkan nama
- Beli produk dengan validasi stok
- Stok otomatis berkurang setelah pembelian
- Transaksi tersimpan di database

### 4. Tracking Pengiriman
- **5 Status Pengiriman**: `pending` → `dikemas` → `dikirim` → `selesai` → `batal`
- Otomatis membuat record pengiriman saat pembelian
- Supplier bisa update status & input nomor resi
- Modal popup untuk update status
- Notifikasi badge untuk pesanan pending
- Pembeli bisa lihat status & resi di riwayat belanja

### 5. Dashboard Statistik

#### Untuk Supplier:
| Statistik | Keterangan |
|-----------|------------|
| Total Produk | Jumlah seluruh produk |
| Total Stok | Total stok keseluruhan |
| Total Supplier | Jumlah supplier terdaftar |
| Peringatan Stok Menipis | Produk dengan stok < 10 |
| Top 5 Produk Terlaris | Produk penjualan tertinggi |
| Penjualan per Jenis Kain | Total terjual berdasarkan jenis kain |
| Grafik Chart.js | Visualisasi bar chart penjualan |
| Top 5 Provinsi Supplier | Provinsi dengan supplier terbanyak |

#### Untuk Pembeli:
| Statistik | Keterangan |
|-----------|------------|
| Total Produk | Jumlah seluruh produk |
| Total Stok | Total stok keseluruhan |
| Total Supplier | Jumlah supplier terdaftar |
| Produk Terbaru | 6 produk terbaru dalam bentuk card |

### 6. Statistik Penjualan per Provinsi
- Menampilkan produk terlaris di setiap provinsi
- Informasi: nama produk, jenis kain, total terjual

### 7. Filter Pencarian Produk Lengkap
| Filter | Keterangan |
|--------|------------|
| Kategori Warna | Dropdown 21 warna |
| Jenis Kain | Dropdown dari database |
| Supplier | Dropdown dari database |
| Provinsi | Dropdown dari database |
| Range Harga | Input min & max |
| Pencarian Nama | Keyword search |
| Sorting | 7 pilihan sorting |
| Reset | Tombol reset semua filter |

### 8. Riwayat Belanja (Pembeli)
- Daftar semua transaksi pembeli
- Detail: tanggal, produk, jumlah, harga, total
- Status pengiriman dengan badge warna

### 9. Pesanan Masuk (Supplier)
- Daftar semua pesanan dari pembeli
- Notifikasi badge untuk pesanan pending
