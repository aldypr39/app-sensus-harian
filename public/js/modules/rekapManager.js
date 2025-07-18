// rekapManager.js
import { dataStore, DataManager } from './dataManager.js';
import { Utils } from './utils.js';



export const RekapitulasiManager = {
  // Update rekapitulasi harian untuk ruangan tertentu dan tanggal tertentu
  updateHarian: (ruanganId, tanggal) => {
    console.log(`Updating rekapitulasi for ${tanggal}...`);

    // Cari entry rekap yang sudah ada untuk tanggal ini
    const existing = dataStore.rekapitulasi_harian.find(r => 
      r.ruangan_id === ruanganId && r.tanggal === tanggal 
    );

    // Hitung pasien masuk pada tanggal ini (aktif + riwayat)
    const pasienMasukDariAktif = dataStore.pasien.filter(p =>
      p.ruangan_id === ruanganId && p.tgl_masuk.split('T')[0] === tanggal
    ).length;

    const pasienMasukDariRiwayat = dataStore.riwayat_pasien.filter(p =>
      p.ruangan_id === ruanganId && p.tgl_masuk.split('T')[0] === tanggal
    ).length;

    const totalPasienMasuk = pasienMasukDariAktif + pasienMasukDariRiwayat;

    // Hitung pasien keluar pada tanggal ini (hanya dari riwayat)
    const pasienKeluarHariIni = dataStore.riwayat_pasien.filter(p =>
      p.ruangan_id === ruanganId && p.tgl_keluar && p.tgl_keluar.split('T')[0] === tanggal
    ).length;

    const inOutSama = dataStore.riwayat_pasien.filter(p =>
      p.ruangan_id === ruanganId &&
      p.tgl_masuk.split('T')[0] === tanggal &&
      p.tgl_keluar &&
      p.tgl_keluar.split('T')[0] === tanggal
    ).length;

    // Hitung pasien awal (sisa kemarin)
    const yesterday = new Date(tanggal);
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    const yesterdayEntry = dataStore.rekapitulasi_harian.find(r => 
      r.ruangan_id === ruanganId && r.tanggal === yesterdayStr
    );
    const pasienAwal = yesterdayEntry ? yesterdayEntry.pasien_sisa : 0;

    // Hitung pasien sisa untuk hari ini
    const pasienSisa = pasienAwal + totalPasienMasuk - pasienKeluarHariIni;

    // === Hitung total hari perawatan pasien yang keluar HARI INI ===
    const pasienKeluarList = dataStore.riwayat_pasien.filter(p =>
      p.ruangan_id === ruanganId && p.tgl_keluar && p.tgl_keluar.split('T')[0] === tanggal
    );
    const totalHariPerawatanKeluar = pasienKeluarList
      .reduce((sum, p) => sum + p.lama_dirawat, 0);
    // ==============================================================

    const pasienKeluarHidupHariIni = pasienKeluarList.filter(p => p.keadaan_keluar !== 'meninggal').length;

    // --- Hitung kematian per kategori ≤48 jam / >48 jam, laki/perempuan ---
    const kematianHariIni = pasienKeluarList.filter(p => p.keadaan_keluar === 'meninggal');
    const matiKurang48L = kematianHariIni.filter(p =>
      p.jenis_kelamin.toUpperCase() === 'L' &&
      Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) <= 2
    ).length;
    const matiKurang48P = kematianHariIni.filter(p =>
      p.jenis_kelamin.toUpperCase() === 'P' &&
      Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) <= 2
    ).length;
    const matiLebih48L = kematianHariIni.filter(p =>
      p.jenis_kelamin.toUpperCase() === 'L' &&
      Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) > 2
    ).length;
    const matiLebih48P = kematianHariIni.filter(p =>
      p.jenis_kelamin.toUpperCase() === 'P' &&
      Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) > 2
    ).length;

    // Susun data rekap untuk tanggal ini
    const rekapData = {
      ruangan_id: ruanganId,
      tanggal: tanggal,
      pasien_awal: pasienAwal,
      pasien_masuk: totalPasienMasuk,
      keluar_hidup: pasienKeluarHidupHariIni,
      pasien_keluar: pasienKeluarHariIni,
      in_out_sama: inOutSama,
      pasien_sisa: pasienSisa,
      lama_dirawat: totalHariPerawatanKeluar,
      hari_perawatan: pasienSisa + inOutSama,
      mati_kurang48_l: matiKurang48L,
      mati_kurang48_p: matiKurang48P,
      mati_lebih48_l: matiLebih48L,
      mati_lebih48_p: matiLebih48P,
      // total kematian hari ini
      jml_mati: matiKurang48L + matiKurang48P + matiLebih48L + matiLebih48P,
      // jika butuh direct count dirujuk / APS / rujukan, tambahkan di sini
      dirujuk: pasienKeluarList.filter(p => p.keadaan_keluar === 'dirujuk').length,
      aps: pasienKeluarList.filter(p => p.keadaan_keluar === 'aps').length,
      kelas: '',
    };

    // Simpan atau update ke dataStore
    if (existing) {
      Object.assign(existing, rekapData);
      console.log(`Updated existing rekapitulasi for ${tanggal}`);
    } else {
      dataStore.rekapitulasi_harian.push(rekapData);
      console.log(`Created new rekapitulasi for ${tanggal}`);
    }

    // === Perbarui entry untuk BESOK (jika ada) ===
    const tomorrow = new Date(tanggal);
    tomorrow.setDate(tomorrow.getDate() + 1);
    const tomorrowStr = tomorrow.toISOString().split('T')[0];
    const tomorrowEntry = dataStore.rekapitulasi_harian.find(r => 
      r.ruangan_id === ruanganId && r.tanggal === tomorrowStr
    );

    if (tomorrowEntry) {
      // Update pasien_awal & pasien_sisa untuk besok
      tomorrowEntry.pasien_awal = pasienSisa;
      tomorrowEntry.pasien_sisa = tomorrowEntry.pasien_awal
                                + tomorrowEntry.pasien_masuk
                                - tomorrowEntry.pasien_keluar;

      // Hitung lama perawatan pasien yang keluar BESOK
      const pasienKeluarTomorrow = dataStore.riwayat_pasien.filter(p =>
        p.ruangan_id === ruanganId && p.tgl_keluar && p.tgl_keluar.split('T')[0] === tomorrowStr
      );
      const totalHariPerawatanTomorrow = pasienKeluarTomorrow
        .reduce((sum, p) => sum + p.lama_dirawat, 0);

      // Assign hari_perawatan untuk besok
      tomorrowEntry.hari_perawatan = tomorrowEntry.pasien_sisa 
                                    + tomorrowEntry.in_out_sama;

      console.log(`Updated tomorrow (${tomorrowStr}) pasien_awal to ${pasienSisa}`);
    }

    // Persist perubahan
    DataManager.save();
  },

  // Ambil rekap berdasarkan ruangan, bulan, dan tahun
  getRekap: (ruanganId, bulan, tahun) => {
    return dataStore.rekapitulasi_harian.filter(r => {
      const tgl = new Date(r.tanggal);
      return r.ruangan_id === ruanganId && 
             tgl.getMonth() === bulan - 1 && 
             tgl.getFullYear() === tahun;
    });
  },

  // Menghasilkan array rekap bulan untuk tabel bulanan
  generateRekapBulan: (ruanganId, bulan, tahun, kelasFilter = '') => {
    const today          = new Date();
    const lastDayOfMonth = new Date(tahun, bulan, 0).getDate();
    const isCurrentMonth = tahun === today.getFullYear() && bulan === today.getMonth() + 1;
    const maxDay         = isCurrentMonth ? today.getDate() : lastDayOfMonth;
    const rekap          = [];

    for (let day = 1; day <= maxDay; day++) {
      const dd      = String(day).padStart(2, '0');
      const mm      = String(bulan).padStart(2, '0');
      const tanggal = `${tahun}-${mm}-${dd}`;

      // Hitung masuk hari ini (pasien aktif + riwayat), terfilter kelas
      const masukAktif = dataStore.pasien.filter(p =>
        p.ruangan_id === ruanganId &&
        p.tgl_masuk.startsWith(tanggal) &&
        (kelasFilter === '' || p.kelas === kelasFilter)
      ).length;
      const masukRiwayat = dataStore.riwayat_pasien.filter(p =>
        p.ruangan_id === ruanganId &&
        p.tgl_masuk.startsWith(tanggal) &&
        (kelasFilter === '' || p.kelas === kelasFilter)
      ).length;
      const masukBaru = masukAktif + masukRiwayat;

      // Hitung keluar hari ini, terfilter kelas
      const keluarList = dataStore.riwayat_pasien.filter(p =>
        p.ruangan_id === ruanganId &&
        p.tgl_keluar?.startsWith(tanggal) &&
        (kelasFilter === '' || p.kelas === kelasFilter)
      );
      const keluarHidup = keluarList.filter(p => p.keadaan_keluar !== 'meninggal').length;

      // Kematian detail
      const kematian = keluarList.filter(p => p.keadaan_keluar === 'meninggal');
      const mati48L = kematian.filter(p =>
        p.jenis_kelamin === 'L' &&
        Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) <= 2
      ).length;
      const mati48P = kematian.filter(p =>
        p.jenis_kelamin === 'P' &&
        Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) <= 2
      ).length;
      const matiLebih48L = kematian.filter(p =>
        p.jenis_kelamin === 'L' &&
        Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) > 2
      ).length;
      const matiLebih48P = kematian.filter(p =>
        p.jenis_kelamin === 'P' &&
        Utils.hitungLamaDirawat(p.tgl_masuk, p.tgl_keluar) > 2
      ).length;
      const totalMati = mati48L + mati48P + matiLebih48L + matiLebih48P;

      // Hitung pasien pindahan masuk hari ini
      const pindahanMasuk = dataStore.pasien.filter(p =>
        p.ruangan_id === ruanganId &&
        p.tgl_masuk.startsWith(tanggal) &&
        p.asal_pasien === 'pindahan' &&
        (kelasFilter === '' || p.kelas === kelasFilter)
      ).length;

      // Hitung pasien dipindahkan keluar hari ini
      const dipindahkan = keluarList.filter(p =>
        p.keadaan_keluar === 'pindah'
      ).length;

      // Hitung sisa & hari perawatan
      const prev = rekap[rekap.length - 1];
      const pasienAwal = prev ? prev.pasien_sisa : 0;
      const pasienSisa = pasienAwal + masukBaru - keluarList.length;
      const totalHariPerawatan = keluarList.reduce((sum, p) =>
        sum + p.lama_dirawat, 0
      );
      const inOutSama = keluarList.filter(p =>
        p.tgl_masuk.startsWith(tanggal) && p.tgl_keluar.startsWith(tanggal)
      ).length;
      const hariPerawatan = pasienSisa + inOutSama;

      // Push entry hari ini
      rekap.push({
        tanggal,
        pasien_awal:     pasienAwal,
        masuk_baru:      masukBaru,
        pindahan:        pindahanMasuk,
        jml_masuk:       masukBaru,
        dipindahkan:     dipindahkan,
        keluar_hidup:    keluarHidup,
        dirujuk:         keluarList.filter(p => p.keadaan_keluar === 'dirujuk').length,
        aps:             keluarList.filter(p => p.keadaan_keluar === 'aps').length,
        mati_kurang48_l: mati48L,
        mati_kurang48_p: mati48P,
        mati_lebih48_l:  matiLebih48L,
        mati_lebih48_p:  matiLebih48P,
        jml_mati:        totalMati,
        jml_keluar:      keluarList.length,
        lama_dirawat:    totalHariPerawatan,
        in_out_sama:     inOutSama,
        pasien_sisa:     pasienSisa,
        hari_perawatan:  hariPerawatan
      });
    }

    return rekap;
  }
  // END generateRekapBulan
};