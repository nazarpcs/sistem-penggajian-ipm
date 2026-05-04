# Dokumen Requirements

## Pendahuluan

Sistem Penggajian Karyawan PT Indah Permata Mandiri (IPM) adalah aplikasi web terintegrasi yang dirancang untuk mengelola seluruh proses penggajian karyawan outsourcing, mulai dari pencatatan data karyawan, input absensi manual, perhitungan gaji otomatis, pembuatan slip gaji, hingga penerbitan invoice ke perusahaan klien. Sistem ini mendukung tiga peran pengguna dengan hak akses berbeda: Admin, Pemilik PT, dan Karyawan.

## Glosarium

- **Sistem**: Aplikasi web Sistem Penggajian Karyawan PT IPM
- **Admin**: Pengguna dengan akses penuh terhadap seluruh fitur sistem
- **Pemilik_PT**: Pengguna dengan akses monitoring dan approval (read-only + approval)
- **Karyawan**: Pengguna dengan akses self-service terhadap data pribadi dan slip gaji
- **PT_Klien**: Perusahaan mitra yang menggunakan jasa outsourcing PT IPM
- **Absensi**: Catatan kehadiran karyawan per hari kerja
- **Slip_Gaji**: Dokumen rincian gaji karyawan dalam satu periode penggajian
- **Invoice**: Dokumen tagihan yang dikirimkan PT IPM kepada PT Klien
- **Rekap_Absensi**: Rangkuman data absensi karyawan dalam satu periode
- **Periode_Penggajian**: Rentang waktu satu bulan yang digunakan sebagai dasar perhitungan gaji
- **Gaji_Pokok**: Komponen gaji dasar yang ditetapkan untuk setiap karyawan
- **Tunjangan**: Komponen tambahan gaji di luar gaji pokok
- **Lembur**: Komponen gaji tambahan berdasarkan jam kerja di luar jam normal
- **Potongan**: Pengurangan gaji berdasarkan ketidakhadiran, keterlambatan, atau komponen lain
- **Fee_Jasa**: Biaya layanan PT IPM yang ditambahkan ke dalam invoice PT Klien
- **RBAC**: Role-Based Access Control, mekanisme pembatasan akses berdasarkan peran pengguna
- **Audit_Log**: Catatan aktivitas pengguna di dalam sistem
- **Validator**: Komponen sistem yang memvalidasi data masukan
- **Kalkulator_Gaji**: Komponen sistem yang menghitung gaji berdasarkan rumus yang dikonfigurasi
- **Generator_Dokumen**: Komponen sistem yang menghasilkan dokumen PDF (slip gaji dan invoice)
- **Nomor_Invoice**: Kode unik yang diberikan secara otomatis pada setiap invoice yang dibuat

---

## Requirements

### Requirement 1: Autentikasi dan Manajemen Akun

**User Story:** Sebagai pengguna sistem, saya ingin dapat login menggunakan email dan password, sehingga saya dapat mengakses fitur sesuai peran saya dengan aman.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan halaman login dengan form email dan password.
2. WHEN pengguna memasukkan email dan password yang valid, THE Sistem SHALL mengautentikasi pengguna dan mengarahkan ke dashboard sesuai perannya.
3. IF pengguna memasukkan email atau password yang tidak valid, THEN THE Sistem SHALL menampilkan pesan kesalahan dan menolak akses.
4. IF pengguna gagal login sebanyak 5 kali berturut-turut, THEN THE Sistem SHALL mengunci akun selama 15 menit dan menampilkan notifikasi kepada pengguna.
5. WHEN pengguna berhasil login, THE Sistem SHALL membuat sesi autentikasi yang kedaluwarsa setelah 8 jam tidak aktif.
6. WHEN pengguna melakukan logout, THE Sistem SHALL menghapus sesi aktif dan mengarahkan ke halaman login.
7. THE Sistem SHALL mengenkripsi password menggunakan algoritma bcrypt sebelum disimpan ke database.
8. THE Sistem SHALL menyediakan fitur reset password melalui email. WHEN pengguna meminta reset password, THE Sistem SHALL mengirimkan tautan reset yang kedaluwarsa dalam 60 menit ke email terdaftar.
9. THE Sistem SHALL menerapkan rate limiting pada endpoint login, membatasi maksimal 10 percobaan login per menit dari satu alamat IP yang sama.

---

### Requirement 2: Role-Based Access Control (RBAC)

**User Story:** Sebagai Admin, saya ingin sistem memiliki pembagian hak akses yang jelas, sehingga setiap pengguna hanya dapat mengakses fitur yang sesuai dengan perannya.

#### Acceptance Criteria

1. THE Sistem SHALL mendukung tiga peran pengguna: Admin, Pemilik_PT, dan Karyawan.
2. WHILE pengguna memiliki peran Admin, THE Sistem SHALL memberikan akses penuh ke seluruh fitur manajemen data, absensi, penggajian, invoice, dan pengaturan sistem.
3. WHILE pengguna memiliki peran Pemilik_PT, THE Sistem SHALL memberikan akses hanya untuk melihat laporan, dashboard, dan melakukan approval invoice.
4. WHILE pengguna memiliki peran Karyawan, THE Sistem SHALL memberikan akses hanya ke data diri sendiri, riwayat absensi pribadi, dan slip gaji pribadi.
5. IF pengguna mencoba mengakses halaman atau fitur di luar hak aksesnya, THEN THE Sistem SHALL menampilkan halaman error 403 dan mencatat percobaan akses tersebut ke Audit_Log.
6. THE Sistem SHALL memvalidasi hak akses pada setiap permintaan HTTP, bukan hanya pada tampilan antarmuka.

---

### Requirement 3: Manajemen Data Karyawan

**User Story:** Sebagai Admin, saya ingin mengelola data lengkap karyawan beserta relasinya ke PT Klien, sehingga informasi karyawan selalu akurat dan terorganisir.

#### Acceptance Criteria

1. THE Sistem SHALL menyimpan data karyawan yang mencakup: nama lengkap, NIK, tanggal lahir, alamat, nomor telepon, email, jabatan, gaji pokok, tanggal bergabung, status aktif, dan relasi ke PT_Klien.
2. WHEN Admin menambahkan karyawan baru, THE Sistem SHALL membuat akun login otomatis untuk karyawan tersebut dengan email sebagai username dan password sementara.
3. WHEN Admin menambahkan karyawan baru, THE Sistem SHALL mengirimkan notifikasi berisi kredensial login ke email karyawan.
4. WHEN Admin memperbarui data karyawan, THE Sistem SHALL menyimpan perubahan dan mencatat aktivitas ke Audit_Log.
5. IF Admin mencoba menghapus karyawan yang masih memiliki data absensi atau slip gaji aktif, THEN THE Sistem SHALL menampilkan peringatan dan meminta konfirmasi sebelum melanjutkan.
6. THE Sistem SHALL mendukung pencarian dan filter karyawan berdasarkan nama, PT_Klien, jabatan, dan status aktif.
7. WHILE karyawan berstatus tidak aktif, THE Sistem SHALL menonaktifkan akun login karyawan tersebut secara otomatis.

---

### Requirement 4: Manajemen Data PT Klien

**User Story:** Sebagai Admin, saya ingin mengelola data perusahaan klien beserta kontrak kerja sama, sehingga pengelompokan karyawan dan penagihan invoice dapat dilakukan dengan tepat.

#### Acceptance Criteria

1. THE Sistem SHALL menyimpan data PT_Klien yang mencakup: nama perusahaan, alamat, nomor telepon, email, nama PIC, nomor kontrak, tanggal mulai kontrak, tanggal berakhir kontrak, dan besaran Fee_Jasa.
2. WHEN Admin menambahkan atau memperbarui data PT_Klien, THE Sistem SHALL menyimpan perubahan dan mencatat aktivitas ke Audit_Log.
3. THE Sistem SHALL menampilkan daftar karyawan yang terdaftar di bawah setiap PT_Klien.
4. IF tanggal berakhir kontrak PT_Klien kurang dari 30 hari dari tanggal hari ini, THEN THE Sistem SHALL menampilkan notifikasi peringatan kepada Admin. THE Sistem SHALL menjalankan pengecekan otomatis setiap hari untuk mendeteksi kontrak PT_Klien yang akan berakhir dalam 30 hari ke depan dan menampilkan notifikasi kepada Admin.
5. THE Sistem SHALL mendukung konfigurasi aturan gaji yang berbeda untuk setiap PT_Klien, mencakup komponen tunjangan dan rumus perhitungan lembur.
6. THE Sistem SHALL menyediakan fitur CRUD untuk konfigurasi aturan gaji per PT_Klien, mencakup: gaji pokok default, jam kerja normal per hari, tarif lembur per jam, potongan per hari alpha, dan daftar komponen tunjangan.
7. WHEN Admin mengubah konfigurasi gaji PT_Klien, THE Sistem SHALL mencatat perubahan ke Audit_Log dan memastikan perubahan hanya berlaku untuk perhitungan gaji periode berikutnya, tidak mengubah data historis.

---

### Requirement 5: Input dan Upload Absensi Manual

**User Story:** Sebagai Admin, saya ingin dapat menginput absensi karyawan secara manual maupun melalui upload file Excel, sehingga proses pencatatan kehadiran dapat dilakukan secara fleksibel.

#### Acceptance Criteria

1. THE Sistem SHALL menyediakan form input absensi manual per karyawan dengan field: tanggal, status kehadiran (Hadir, Izin, Sakit, Alpha), jam masuk, jam keluar, dan keterangan.
2. THE Sistem SHALL menyediakan fitur upload file Excel untuk input absensi massal dengan format template yang telah ditentukan.
3. WHEN Admin mengupload file Excel absensi, THE Validator SHALL terlebih dahulu memvalidasi seluruh data secara sinkron (format file, kelengkapan kolom wajib, kevalidan data) sebelum proses penyimpanan dimulai.
4. IF file Excel yang diupload mengandung data yang tidak valid, THEN THE Validator SHALL menampilkan daftar baris yang bermasalah beserta keterangan kesalahannya tanpa menyimpan data apapun.
5. IF validasi berhasil, THEN THE Sistem SHALL memproses penyimpanan data secara asinkron menggunakan background job dan menampilkan notifikasi progres kepada Admin.
6. THE Sistem SHALL mencegah duplikasi data absensi untuk kombinasi karyawan dan tanggal yang sama.
7. IF Admin mencoba menyimpan absensi dengan kombinasi karyawan dan tanggal yang sudah ada, THEN THE Sistem SHALL menampilkan peringatan dan menawarkan opsi untuk menimpa data lama.

---

### Requirement 6: Rekap dan Pengolahan Absensi

**User Story:** Sebagai Admin, saya ingin sistem merekap absensi karyawan secara otomatis per periode, sehingga data yang digunakan untuk perhitungan gaji selalu akurat.

#### Acceptance Criteria

1. WHEN Admin memilih periode penggajian dan PT_Klien, THE Sistem SHALL menghasilkan Rekap_Absensi yang mencakup: total hari hadir, total hari izin, total hari sakit, total hari alpha, dan total jam lembur untuk setiap karyawan.
2. THE Kalkulator_Gaji SHALL menghitung jam lembur berdasarkan selisih jam keluar dengan jam kerja normal yang dikonfigurasi per PT_Klien.
3. THE Sistem SHALL menampilkan Rekap_Absensi dalam bentuk tabel yang dapat difilter berdasarkan PT_Klien dan Periode_Penggajian.
4. IF terdapat karyawan tanpa data absensi dalam suatu Periode_Penggajian, THEN THE Sistem SHALL menampilkan peringatan kepada Admin sebelum proses perhitungan gaji dilanjutkan.
5. WHEN Admin mengonfirmasi Rekap_Absensi, THE Sistem SHALL mengunci data absensi pada periode tersebut agar tidak dapat diubah tanpa izin Admin.
6. WHEN Admin membuka kunci periode absensi yang sudah terkunci, THE Sistem SHALL mencatat aktivitas ke Audit_Log beserta identitas Admin dan alasan pembukaan kunci.
7. THE Sistem SHALL hanya mengizinkan pengguna dengan peran Admin untuk membuka kunci periode absensi.

---

### Requirement 7: Perhitungan Gaji Otomatis

**User Story:** Sebagai Admin, saya ingin sistem menghitung gaji karyawan secara otomatis berdasarkan rekap absensi dan aturan gaji yang berlaku, sehingga proses penggajian lebih cepat dan akurat.

#### Acceptance Criteria

1. THE Kalkulator_Gaji SHALL menghitung gaji menggunakan rumus: Gaji Bersih = Gaji_Pokok + Total_Tunjangan + Total_Lembur - Total_Potongan.
2. THE Kalkulator_Gaji SHALL menghitung Total_Lembur berdasarkan tarif lembur per jam yang dikonfigurasi per PT_Klien dikalikan total jam lembur dari Rekap_Absensi.
3. THE Kalkulator_Gaji SHALL menghitung Total_Potongan berdasarkan jumlah hari alpha dikalikan nilai potongan per hari yang dikonfigurasi per PT_Klien.
4. WHEN Admin menjalankan proses perhitungan gaji untuk suatu Periode_Penggajian, THE Kalkulator_Gaji SHALL menghasilkan data gaji untuk seluruh karyawan aktif yang terdaftar pada PT_Klien yang dipilih.
5. WHEN perhitungan gaji selesai, THE Sistem SHALL menyimpan rincian komponen gaji dan membuatnya tersedia untuk proses pembuatan Slip_Gaji.
6. IF aturan gaji suatu PT_Klien diubah setelah perhitungan gaji dilakukan, THEN THE Sistem SHALL mempertahankan data gaji yang sudah dihitung dan tidak mengubahnya secara otomatis.
7. THE Sistem SHALL mendukung konfigurasi komponen tunjangan yang berbeda-beda per PT_Klien (misalnya: tunjangan transport, tunjangan makan, tunjangan jabatan).
8. THE Kalkulator_Gaji SHALL memastikan Gaji Bersih tidak pernah bernilai kurang dari 0 (nol). IF hasil perhitungan menghasilkan nilai negatif, THEN THE Sistem SHALL menetapkan Gaji Bersih = 0 dan menampilkan peringatan kepada Admin.

---

### Requirement 8: Slip Gaji Karyawan

**User Story:** Sebagai Karyawan, saya ingin dapat melihat dan mengunduh slip gaji saya setiap bulan, sehingga saya memiliki bukti penerimaan gaji yang jelas.

#### Acceptance Criteria

1. WHEN perhitungan gaji untuk suatu Periode_Penggajian selesai, THE Sistem SHALL membuat Slip_Gaji untuk setiap karyawan yang dapat diakses melalui akun masing-masing.
2. THE Slip_Gaji SHALL menampilkan rincian: nama karyawan, PT_Klien, periode, Gaji_Pokok, rincian setiap Tunjangan, Total_Lembur beserta jam lembur, rincian setiap Potongan, dan Gaji Bersih.
3. WHEN Karyawan mengakses halaman slip gaji, THE Sistem SHALL menampilkan daftar Slip_Gaji milik karyawan tersebut yang diurutkan dari periode terbaru.
4. WHEN Karyawan memilih Slip_Gaji tertentu dan mengklik tombol unduh, THE Generator_Dokumen SHALL menghasilkan file PDF Slip_Gaji dan mengunduhnya ke perangkat karyawan.
5. THE Sistem SHALL memastikan Karyawan hanya dapat mengakses Slip_Gaji miliknya sendiri.
6. WHILE Admin mengakses fitur slip gaji, THE Sistem SHALL menampilkan Slip_Gaji seluruh karyawan dengan kemampuan filter berdasarkan PT_Klien, karyawan, dan periode.

---

### Requirement 9: Pembuatan dan Pengelolaan Invoice

**User Story:** Sebagai Admin, saya ingin dapat membuat invoice ke PT Klien secara otomatis berdasarkan rekap gaji, sehingga proses penagihan lebih efisien dan terstandarisasi.

#### Acceptance Criteria

1. WHEN Admin memilih PT_Klien dan Periode_Penggajian untuk membuat invoice, THE Sistem SHALL menghasilkan Invoice yang berisi: Nomor_Invoice unik, tanggal pembuatan, data PT_Klien, rincian gaji seluruh karyawan, subtotal gaji, Fee_Jasa PT IPM, pajak (opsional), dan total tagihan.
2. THE Sistem SHALL menghasilkan Nomor_Invoice dengan format unik yang mencakup kode PT_Klien, tahun, bulan, dan nomor urut (contoh: IPM-ABC-2025-06-001).
3. IF Invoice untuk kombinasi PT_Klien dan Periode_Penggajian yang sama sudah pernah dibuat, THEN THE Sistem SHALL menampilkan peringatan dan mencegah pembuatan duplikat.
4. WHEN Invoice berhasil dibuat, THE Sistem SHALL menetapkan status invoice sebagai "Menunggu Approval".
5. WHEN Pemilik_PT melakukan approval terhadap Invoice, THE Sistem SHALL mengubah status invoice menjadi "Disetujui" dan mencatat waktu approval beserta identitas Pemilik_PT ke Audit_Log.
6. IF Pemilik_PT menolak Invoice, THEN THE Sistem SHALL mengubah status invoice menjadi "Ditolak" dan mewajibkan Pemilik_PT mengisi alasan penolakan.
7. WHEN Pemilik_PT menolak Invoice, THE Sistem SHALL mencatat identitas Pemilik_PT yang menolak, waktu penolakan, dan alasan penolakan ke dalam data invoice.
8. WHEN Invoice berstatus "Disetujui", THE Generator_Dokumen SHALL memungkinkan Admin untuk mengunduh Invoice dalam format PDF.
9. THE Sistem SHALL menampilkan daftar Invoice dengan filter berdasarkan PT_Klien, Periode_Penggajian, dan status approval.
10. WHEN dua atau lebih proses pembuatan invoice terjadi secara bersamaan, THE Sistem SHALL menggunakan database-level locking untuk memastikan Nomor_Invoice yang dihasilkan selalu unik dan tidak ada duplikasi.

---

### Requirement 10: Dashboard dan Laporan

**User Story:** Sebagai Admin dan Pemilik PT, saya ingin memiliki dashboard dan laporan yang informatif, sehingga saya dapat memantau kondisi keuangan dan operasional secara real-time.

#### Acceptance Criteria

1. WHILE pengguna memiliki peran Admin, THE Sistem SHALL menampilkan dashboard yang mencakup: total karyawan aktif, total PT_Klien aktif, ringkasan penggajian bulan berjalan, dan daftar invoice yang menunggu approval.
2. WHILE pengguna memiliki peran Pemilik_PT, THE Sistem SHALL menampilkan dashboard yang mencakup: total pengeluaran gaji per bulan, grafik tren pengeluaran gaji 12 bulan terakhir, dan daftar invoice yang memerlukan approval.
3. THE Sistem SHALL menyediakan laporan absensi yang dapat difilter berdasarkan PT_Klien, karyawan, dan rentang tanggal.
4. THE Sistem SHALL menyediakan laporan penggajian yang dapat difilter berdasarkan PT_Klien dan Periode_Penggajian.
5. THE Sistem SHALL menyediakan laporan invoice yang dapat difilter berdasarkan PT_Klien, Periode_Penggajian, dan status.
6. WHEN pengguna mengekspor laporan, THE Generator_Dokumen SHALL menghasilkan file dalam format PDF atau Excel sesuai pilihan pengguna.
7. THE Sistem SHALL menampilkan grafik pengeluaran gaji per PT_Klien dalam bentuk diagram batang atau garis pada dashboard Pemilik_PT.

---

### Requirement 11: Audit Log Aktivitas

**User Story:** Sebagai Admin, saya ingin sistem mencatat seluruh aktivitas penting pengguna, sehingga setiap perubahan data dapat ditelusuri untuk keperluan audit dan keamanan.

#### Acceptance Criteria

1. THE Sistem SHALL mencatat setiap aktivitas berikut ke Audit_Log: login/logout, penambahan/perubahan/penghapusan data karyawan, penambahan/perubahan data PT_Klien, input/upload absensi, proses perhitungan gaji, pembuatan/approval/penolakan invoice, dan perubahan pengaturan sistem.
2. THE Audit_Log SHALL menyimpan informasi: identitas pengguna, peran pengguna, jenis aktivitas, waktu aktivitas, dan data yang diubah (sebelum dan sesudah perubahan).
3. WHILE pengguna memiliki peran Admin, THE Sistem SHALL menyediakan halaman untuk melihat dan memfilter Audit_Log berdasarkan pengguna, jenis aktivitas, dan rentang waktu.
4. THE Sistem SHALL menyimpan data Audit_Log selama minimal 1 tahun.
5. IF terjadi percobaan akses tidak sah, THEN THE Sistem SHALL mencatat percobaan tersebut ke Audit_Log beserta alamat IP pengguna.

---

### Requirement 12: Keamanan Data

**User Story:** Sebagai Admin, saya ingin data karyawan dan keuangan terlindungi dengan baik, sehingga informasi sensitif tidak dapat diakses oleh pihak yang tidak berwenang.

#### Acceptance Criteria

1. THE Sistem SHALL memastikan setiap endpoint API diproteksi dengan autentikasi token sesi yang valid.
2. THE Sistem SHALL memvalidasi dan membersihkan seluruh input pengguna untuk mencegah serangan SQL Injection dan Cross-Site Scripting (XSS).
3. THE Sistem SHALL menggunakan HTTPS untuk seluruh komunikasi antara browser dan server.
4. WHILE Karyawan mengakses data melalui API, THE Sistem SHALL memastikan respons hanya mengandung data milik karyawan yang sedang login.
5. IF sesi pengguna kedaluwarsa, THEN THE Sistem SHALL mengarahkan pengguna ke halaman login tanpa menyimpan data yang belum tersimpan.
6. THE Sistem SHALL menerapkan proteksi CSRF pada seluruh form yang melakukan perubahan data.
