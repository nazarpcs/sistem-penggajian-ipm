/**
 * Test data fixtures untuk E2E test Sistem Penggajian PT IPM.
 * Sesuai dengan database seeder (AdminSeeder, KaryawanSeeder, PtKlienSeeder).
 */

export const USERS = {
  admin: {
    email: 'admin@ipm.test',
    password: 'password',
    role: 'admin',
    dashboardUrl: '/admin/dashboard',
    dashboardTitle: 'Dashboard',
  },
  pemilik_pt: {
    email: 'owner@ptabc.co.id',
    password: 'password',
    role: 'pemilik_pt',
    dashboardUrl: '/owner/dashboard',
    dashboardTitle: 'Dashboard',
  },
  karyawan: {
    email: 'andi.pratama@ipm.test',
    password: 'password',
    role: 'karyawan',
    dashboardUrl: '/karyawan/profil',
    dashboardTitle: 'Profil',
  },
} as const;

export const INVALID_CREDENTIALS = {
  wrongEmail: 'nonexistent@example.com',
  wrongPassword: 'wrongpassword123',
  emptyEmail: '',
  emptyPassword: '',
  sqlInjection: "' OR 1=1 --",
  xssPayload: '<script>alert("xss")</script>',
};

export const KARYAWAN_VALID = {
  nama_lengkap: 'Test Karyawan Playwright',
  nik: '3201234567890001',
  email: 'playwright.test@example.com',
  jabatan: 'Staff IT',
  gaji_pokok: '5000000',
  tanggal_bergabung: '2025-01-15',
};

export const KARYAWAN_INVALID = {
  empty: { nama_lengkap: '', nik: '', email: '' },
  duplicateNik: { nik: '3201234567890001' },
  invalidEmail: { email: 'bukan-email' },
  negativeGaji: { gaji_pokok: '-1000' },
};

export const PT_KLIEN_VALID = {
  nama: 'PT Test Playwright',
  alamat: 'Jl. Test No. 1',
  telepon: '021-1234567',
  email: 'test@ptplaywright.com',
  nama_pic: 'PIC Test',
  nomor_kontrak: 'KTR-PW-2025-001',
  fee_jasa: '5000000',
};

export const ABSENSI_VALID = {
  tanggal: '2025-06-15',
  status_kehadiran: 'Hadir',
  jam_masuk: '08:00',
  jam_keluar: '17:00',
};

export const ROUTES = {
  login: '/login',
  logout: '/logout',
  forgotPassword: '/password/forgot',
  admin: {
    dashboard: '/admin/dashboard',
    karyawan: '/admin/karyawan',
    ptKlien: '/admin/pt-klien',
    absensi: '/admin/absensi',
    penggajian: '/admin/penggajian',
    invoice: '/admin/invoice',
    auditLog: '/admin/audit-log',
    laporan: {
      absensi: '/admin/laporan/absensi',
      penggajian: '/admin/laporan/penggajian',
      invoice: '/admin/laporan/invoice',
    },
  },
  owner: {
    dashboard: '/owner/dashboard',
    invoice: '/owner/invoice',
  },
  karyawan: {
    profil: '/karyawan/profil',
    absensi: '/karyawan/absensi',
    slipGaji: '/karyawan/slip-gaji',
  },
} as const;
