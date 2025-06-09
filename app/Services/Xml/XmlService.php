<?php

namespace App\Services\Xml;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class XmlService
{
    public function insertDataFromXml130ToDB()
    {
        $this->readFileXml130();
    }
    public function readFileXml130()
    {
        $directory = 'C:\Users\tranl\Downloads\130_test';

        // Kiểm tra thư mục
        if (!is_dir($directory)) {
            throw new \Exception("Thư mục không tồn tại: $directory");
        }

        // Lấy tất cả file .xml
        $files = File::files($directory);

        foreach ($files as $file) {
            $xmlContent = File::get($file);
            $xml = simplexml_load_string($xmlContent);

            if (!$xml) {
                Log::error("Không thể parse XML: {$file->getFilename()}");
                continue;
            }
            $danhSachHoSo = $xml->xpath('//DANHSACHHOSO'); // Lấy cái DANHSACHHOSO bất kể cấp bậc, vị trí
            DB::connection('oracle_his')->transaction(function () use ($danhSachHoSo) {
                $this->insertDBXML($danhSachHoSo);
            });
        }
    }

    public function handleDecodeBase64NoiDungFile($noiDungBase64)
    {
        // Giải mã base64
        $decodedXml = base64_decode($noiDungBase64);
        $data = simplexml_load_string($decodedXml);
        return $data;
    }

    public function insertDBXML($danhSachHoSo)
    {
        foreach ($danhSachHoSo as $hoSo) {
            foreach ($hoSo as $fileHoSo) {
                foreach ($fileHoSo as $item) {
                    $loaiHoSo = (string) $item->LOAIHOSO;
                    $noiDungFile = $this->handleDecodeBase64NoiDungFile($item->NOIDUNGFILE);
                    switch ($loaiHoSo) {
                        case 'XML1':
                            $this->handleInsertDBXML1($noiDungFile); // Tổng hợp
                            break;
                        case 'XML2':
                            $this->handleInsertDBXML2($noiDungFile); // Thuốc
                            break;
                        case 'XML3':
                            $this->handleInsertDBXML3($noiDungFile); // Dịch vụ kỹ thuật, Vật tư y tế
                            break;
                        case 'XML4':
                            $this->handleInsertDBXML4($noiDungFile); // Dịch vụ CLS
                            break;
                        case 'XML5':
                            $this->handleInsertDBXML5($noiDungFile); // Diễn biến lâm sàng
                            break;
                        case 'XML6':
                            $this->handleInsertDBXML6($noiDungFile); // HIV-AIDS
                            break;
                        case 'XML7':
                            $this->handleInsertDBXML7($noiDungFile); // Giấy ra viện
                            break;
                        case 'XML8':
                            $this->handleInsertDBXML8($noiDungFile); // Tóm tắt hồ sơ bệnh án
                            break;
                        case 'XML9':
                            $this->handleInsertDBXML9($noiDungFile); // Giấy chứng sinh
                            break;
                        case 'XML10':
                            $this->handleInsertDBXML10($noiDungFile); // Bảng nghỉ dưỡng thai
                            break;
                        case 'XML11':
                            $this->handleInsertDBXML11($noiDungFile); // Giấy nghỉ hưởng BHXH
                            break;
                        case 'XML12':
                            $this->handleInsertDBXML12($noiDungFile); // Dữ liệu giám định y khoa
                            break;
                        case 'XML13':
                            $this->handleInsertDBXML13($noiDungFile); // Giấy chuyển tuyến BHYT
                            break;
                        case 'XML14':
                            $this->handleInsertDBXML14($noiDungFile); // Giấy hẹn khám lại
                            break;
                        case 'XML15':
                            $this->handleInsertDBXML15($noiDungFile); // Lao
                            break;
                        default:
                            break;
                    }
                }
            }
        }
    }
    public function handleInsertDBXML1($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $stt = (int) $noiDungFile->STT;
        $maBenhNhan = (string) $noiDungFile->MA_BN;
        $hoTen = (string) $noiDungFile->HO_TEN;
        $soCCCD = (string) $noiDungFile->SO_CCCD;
        $ngaySinh = (int) $noiDungFile->NGAY_SINH;
        $gioiTinh = (int) $noiDungFile->GIOI_TINH;
        $nhomMau = (string) $noiDungFile->NHOM_MAU;
        $maQuocTich = (int) $noiDungFile->MA_QUOC_TICH;
        $maDanToc = (int) $noiDungFile->MA_DAN_TOC;
        $maNgheNghiep = (string) $noiDungFile->MA_NGHE_NGHIEP;
        $diaChi = (string) $noiDungFile->DIA_CHI;
        $maTinhCuTru = (string) $noiDungFile->MATINH_CU_TRU;
        $maHuyenCuTru = (string) $noiDungFile->MAHUYEN_CU_TRU;
        $maXaCuTru = (string) $noiDungFile->MAXA_CU_TRU;
        $dienThoai = (string) $noiDungFile->DIEN_THOAI;
        $maTheBHYT = (string) $noiDungFile->MA_THE_BHYT;
        $maDKBD = (string) $noiDungFile->MA_DKBD;
        $giaTriTheTu = (string) $noiDungFile->GT_THE_TU;
        $giaTriTheDen = (string) $noiDungFile->GT_THE_DEN;
        $ngayMienCungChiTra = (int) $noiDungFile->NGAY_MIEN_CCT;
        $lyDoVaoVien = (string) $noiDungFile->LY_DO_VV;
        $lyDoVaoNoiTru = (string) $noiDungFile->LY_DO_VNT;
        $maLyDoVaoNoiTru = (string) $noiDungFile->MA_LY_DO_VNT;
        $chanDoanVao = (string) $noiDungFile->CHAN_DOAN_VAO;
        $chanDoanRaVien = (string) $noiDungFile->CHAN_DOAN_RV;
        $maBenhChinh = (string) $noiDungFile->MA_BENH_CHINH;
        $maBenhKemTheo = (string) $noiDungFile->MA_BENH_KT;
        $maBenhYHCT = (string) $noiDungFile->MA_BENH_YHCT;
        $maPTTTQuaTrinh = (string) $noiDungFile->MA_PTTT_QT;
        $maDoiTuongKhamChuaBenh = (string) $noiDungFile->MA_DOITUONG_KCB;
        $maNoiDi = (string) $noiDungFile->MA_NOI_DI;
        $maNoiDen = (string) $noiDungFile->MA_NOI_DEN;
        $maTaiNan = (string) $noiDungFile->MA_TAI_NAN;
        $ngayVao = (int) $noiDungFile->NGAY_VAO;
        $ngayVaoNoiTru = (int) $noiDungFile->NGAY_VAO_NOI_TRU;
        $ngayRa = (int) $noiDungFile->NGAY_RA;
        $giayChuyenTuyen = (string) $noiDungFile->GIAY_CHUYEN_TUYEN;
        $soNgayDieuTri = (int) $noiDungFile->SO_NGAY_DIEU_TRI;
        $phuongPhapDieuTri = (string) $noiDungFile->PP_DIEU_TRI;
        $ketQuaDieuTri = (int) $noiDungFile->KET_QUA_DTRI;
        $maLoaiRaVien = (int) $noiDungFile->MA_LOAI_RV;
        $ghiChu = (string) $noiDungFile->GHI_CHU;
        $ngayThanhToan = (int) $noiDungFile->NGAY_TTOAN;
        $tienThuoc = (float) $noiDungFile->T_THUOC;
        $tienVatTuYTe = (float) $noiDungFile->T_VTYT;
        $tienTongChiBenhVien = (float) $noiDungFile->T_TONGCHI_BV;
        $tienTongChiBaoHiem = (float) $noiDungFile->T_TONGCHI_BH;
        $tienBenhNhanTuTra = (float) $noiDungFile->T_BNTT;
        $tienBenhNhanCungChiTra = (float) $noiDungFile->T_BNCCT;
        $tienBaoHiemThanhToan = (float) $noiDungFile->T_BHTT;
        $tienNguonKhac = (float) $noiDungFile->T_NGUOCKHAC;
        $tienBaoHiemThanhToanGoiDichVu = (float) $noiDungFile->T_BHTT_GDV;
        $namQuyetToan = (int) $noiDungFile->NAM_QT;
        $thangQuyetToan = (int) $noiDungFile->THANG_QT;
        $maLoaiKhamChuaBenh = (string) $noiDungFile->MA_LOAI_KCB;
        $maKhoa = (string) $noiDungFile->MA_KHOA;
        $maCoSoKhamChuaBenh = (string) $noiDungFile->MA_CSKCB;
        $maKhuVuc = (string) $noiDungFile->MA_KHUVUC;
        $canNang = (string) $noiDungFile->CAN_NANG;
        $canNangCon = (string) $noiDungFile->CAN_NANG_CON;
        $namNamLienTuc = (int) $noiDungFile->NAM_NAM_LIEN_TUC;
        $ngayTaiKham = (string) $noiDungFile->NGAY_TAI_KHAM;
        $maHoSoBenhAn = (string) $noiDungFile->MA_HSBA;
        $maThuTruongDonVi = (string) $noiDungFile->MA_TTDV;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML2($noiDungFile)
    {
        foreach ($noiDungFile as $danhSachChiTietThuoc) {
            foreach ($danhSachChiTietThuoc as $chiTietThuoc) {
                $maLienKet = (string) $chiTietThuoc->MA_LK;
                $stt = (int) $chiTietThuoc->STT;
                $maThuoc = (string) $chiTietThuoc->MA_THUOC;
                $maPhuongPhapCheBien = (string) $chiTietThuoc->MA_PP_CHEBIEN;
                $maCoSoKhamChuaBenhThuoc = (string) $chiTietThuoc->MA_CSKCB_THUOC;
                $maNhom = (int) $chiTietThuoc->MA_NHOM;
                $tenThuoc = (string) $chiTietThuoc->TEN_THUOC;
                $donViTinh = (string) $chiTietThuoc->DON_VI_TINH;
                $hamLuong = (string) $chiTietThuoc->HAM_LUONG;
                $duongDung = (string) $chiTietThuoc->DUONG_DUNG;
                $dangBaoChe = (string) $chiTietThuoc->DANG_BAO_CHE;
                $lieuDung = (string) $chiTietThuoc->LIEU_DUNG;
                $cachDung = (string) $chiTietThuoc->CACH_DUNG;
                $soDangKy = (string) $chiTietThuoc->SO_DANG_KY;
                $thongTinThau = (string) $chiTietThuoc->TT_THAU;
                $phamVi = (int) $chiTietThuoc->PHAM_VI;
                $tyLeThanhToanBaoHiem = (int) $chiTietThuoc->TYLE_TT_BH;
                $soLuong = (float) $chiTietThuoc->SO_LUONG;
                $donGia = (float) $chiTietThuoc->DON_GIA;
                $thanhTienBenhVien = (float) $chiTietThuoc->THANH_TIEN_BV;
                $thanhTienBaoHiem = (float) $chiTietThuoc->THANH_TIEN_BH;
                $tienNguocKhacNganSachNhaNuoc = (float) $chiTietThuoc->T_NGUONKHAC_NSNN;
                $tienNguocKhacVatTuNgoaiNuoc = (float) $chiTietThuoc->T_NGUOCKHAC_VTNN;
                $tienNguocKhacVatTuTrongNuoc = (float) $chiTietThuoc->T_NGUOCKHAC_VTTN;
                $tienNguocKhacConLai = (float) $chiTietThuoc->T_NGUOCKHAC_CL;
                $tienNguocKhac = (float) $chiTietThuoc->T_NGUOCKHAC;
                $mucHuong = (int) $chiTietThuoc->MUC_HUONG;
                $tienBenhNhanTuTra = (float) $chiTietThuoc->T_BNTT;
                $tienBenhNhanCungChiTra = (float) $chiTietThuoc->T_BNCCT;
                $tienBaoHiemThanhToan = (float) $chiTietThuoc->T_BHTT;
                $maKhoa = (string) $chiTietThuoc->MA_KHOA;
                $maBacSi = (string) $chiTietThuoc->MA_BAC_SI;
                $maDichVu = (string) $chiTietThuoc->MA_DICH_VU;
                $ngayYLenh = (int) $chiTietThuoc->NGAY_YL;
                $ngayThucHienYLenh = (int) $chiTietThuoc->NGAY_TH_YL;
                $maPhuongThucThanhToan = (int) $chiTietThuoc->MA_PTTT;
                $nguonCungTra = (int) $chiTietThuoc->NGUON_CTRA;
                $vetTHuongTaiPhat = (int) $chiTietThuoc->VET_THUONG_TP;
                $duPhong = (string) $chiTietThuoc->DU_PHONG;
            }
        }
    }
    public function handleInsertDBXML3($noiDungFile)
    {
        foreach ($noiDungFile as $danhSachChiTietDichVuKyThuat) {
            foreach ($danhSachChiTietDichVuKyThuat as $chiTietDichVuKyThuat) {
                $maLienKet = (string) $chiTietDichVuKyThuat->MA_LK;
                $stt = (int) $chiTietDichVuKyThuat->STT;
                $maDichVu = (string) $chiTietDichVuKyThuat->MA_DICH_VU;
                $maPhauThuatThuThuatQuiTrinh = (string) $chiTietDichVuKyThuat->MA_PTTT_QT;
                $maVatTu = (string) $chiTietDichVuKyThuat->MA_VAT_TU;
                $maNhom = (int) $chiTietDichVuKyThuat->MA_NHOM;
                $goiVTYT = (string) $chiTietDichVuKyThuat->GOI_VTYT;
                $tenVatTu = (string) $chiTietDichVuKyThuat->TEN_VAT_TU;
                $tenDichVu = (string) $chiTietDichVuKyThuat->TEN_DICH_VU;
                $maXangDau = (string) $chiTietDichVuKyThuat->MA_XANG_DAU;
                $donViTinh = (string) $chiTietDichVuKyThuat->DON_VI_TINH;
                $phamVi = (int) $chiTietDichVuKyThuat->PHAM_VI;
                $soLuong = (float) $chiTietDichVuKyThuat->SO_LUONG;
                $donGiaBenhVien = (float) $chiTietDichVuKyThuat->DON_GIA_BV;
                $donGiaBaoHiem = (float) $chiTietDichVuKyThuat->DON_GIA_BH;
                $thongTinThau = (string) $chiTietDichVuKyThuat->TT_THAU;
                $tyLeThanhToanDichVu = (float) $chiTietDichVuKyThuat->TYLE_TT_DV;
                $tyLeThanhToanBaoHiem = (float) $chiTietDichVuKyThuat->TYLE_TT_BH;
                $thanhTienBenhVien = (float) $chiTietDichVuKyThuat->THANH_TIEN_BV;
                $thanhTienBaoHiem = (float) $chiTietDichVuKyThuat->THANH_TIEN_BH;
                $tienTranThanhToan = (float) $chiTietDichVuKyThuat->T_TRANTT;
                $mucHuong = (int) $chiTietDichVuKyThuat->MUC_HUONG;
                $tienNguocKhacNganSachNhaNuoc = (float) $chiTietDichVuKyThuat->T_NGUONKHAC_NSNN;
                $tienNguocKhacVatTuNgoaiNuoc = (float) $chiTietDichVuKyThuat->T_NGUOCKHAC_VTNN;
                $tienNguocKhacVatTuTrongNuoc = (float) $chiTietDichVuKyThuat->T_NGUOCKHAC_VTTN;
                $tienNguocKhacConLai = (float) $chiTietDichVuKyThuat->T_NGUOCKHAC_CL;
                $tienNguocKhac = (float) $chiTietDichVuKyThuat->T_NGUOCKHAC;
                $tienBenhNhanTuTra = (float) $chiTietDichVuKyThuat->T_BNTT;
                $tienBenhNhanCungChiTra = (float) $chiTietDichVuKyThuat->T_BNCCT;
                $tienBaoHiemThanhToan = (float) $chiTietDichVuKyThuat->T_BHTT;
                $maKhoa = (string) $chiTietDichVuKyThuat->MA_KHOA;
                $maGiuong = (string) $chiTietDichVuKyThuat->MA_GIUONG;
                $maBacSi = (string) $chiTietDichVuKyThuat->MA_BAC_SI;
                $nguoiThucHien = (string) $chiTietDichVuKyThuat->NGUOI_THUC_HIEN;
                $maBenh = (string) $chiTietDichVuKyThuat->MA_BENH;
                $maYHocCoTruyen = (string) $chiTietDichVuKyThuat->MA_YHCT;
                $ngayYLenh = (int) $chiTietDichVuKyThuat->NGAY_YL;
                $ngayThucHienYLenh = (int) $chiTietDichVuKyThuat->NGAY_TH_YL;
                $ngayKetQua = (int) $chiTietDichVuKyThuat->NGAY_KQ;
                $maPhuongThucThanhToan = (int) $chiTietDichVuKyThuat->MA_PTTT;
                $vetThuongTaiPhat = (int) $chiTietDichVuKyThuat->VET_THUONG_TP;
                $phuongPhapVoCam = (int) $chiTietDichVuKyThuat->PP_VO_CAM;
                $viTriThucHienDichVuKyThuat = (int) $chiTietDichVuKyThuat->VI_TRI_TH_DVKT;
                $maMay = (string) $chiTietDichVuKyThuat->MA_MAY;
                $maHieuSanPham = (string) $chiTietDichVuKyThuat->MA_HIEU_SP;
                $taiSuDung = (int) $chiTietDichVuKyThuat->TAI_SU_DUNG;
                $duPhong = (string) $chiTietDichVuKyThuat->DU_PHONG;
            }
        }
    }
    public function handleInsertDBXML4($noiDungFile)
    {
        foreach ($noiDungFile as $danhSachChiTietCLS) {
            foreach ($danhSachChiTietCLS as $chiTietCLS) {
                $maLienKet = (string) $chiTietCLS->MA_LK;
                $stt = (int) $chiTietCLS->STT;
                $maDichVu = (string) $chiTietCLS->MA_DICH_VU;
                $maChiSo = (string) $chiTietCLS->MA_CHI_SO;
                $tenChiSo = (string) $chiTietCLS->TEN_CHI_SO;
                $giaTri = (string) $chiTietCLS->GIA_TRI;
                $donViDo = (string) $chiTietCLS->DON_VI_DO;
                $moTa = (string) $chiTietCLS->MO_TA;
                $ketLuan = (string) $chiTietCLS->KET_LUAN;
                $ngayKetQua = (int) $chiTietCLS->NGAY_KQ;
                $maBacSiDocKetQua = (string) $chiTietCLS->MA_BS_DOC_KQ;
                $duPhong = (string) $chiTietCLS->duPhong;
            }
        }
    }
    public function handleInsertDBXML5($noiDungFile)
    {
        foreach ($noiDungFile as $danhSachChiTietDienBienBenh) {
            foreach ($danhSachChiTietDienBienBenh as $chiTietDienBienBenh) {
                $maLienKet = (string) $chiTietDienBienBenh->MA_LK;
                $stt = (int) $chiTietDienBienBenh->STT;
                $dienBienLamSang = (string) $chiTietDienBienBenh->DIEN_BIEN_LS;
                $giaiDoanBenh = (string) $chiTietDienBienBenh->GIAI_DOAN_BENH;
                $hoiChan = (string) $chiTietDienBienBenh->HOI_CHAN;
                $phauThuat = (string) $chiTietDienBienBenh->PHAU_THUAT;
                $thoiDiemDienBienLamSang = (string) $chiTietDienBienBenh->THOI_DIEM_DBLS;
                $nguoiThucHien = (string) $chiTietDienBienBenh->NGUOI_THUC_HIEN;
                $duPhong = (string) $chiTietDienBienBenh->duPhong;
            }
        }
    }
    public function handleInsertDBXML6($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $maTheBHYT = (string) $noiDungFile->MA_THE_BHYT;
        $soCCCD = (string) $noiDungFile->SO_CCCD;
        $ngaySinh = (string) $noiDungFile->NGAY_SINH;
        $gioiTinh = (int) $noiDungFile->GIOI_TINH;
        $diaChi = (string) $noiDungFile->DIA_CHI;
        $maTinhCuTru = (string) $noiDungFile->MATINH_CU_TRU;
        $maHuyenCuTru = (string) $noiDungFile->MAHUYEN_CU_TRU;
        $maXaCuTru = (string) $noiDungFile->MAXA_CU_TRU;
        $ngayKhangDinhHIV = (int) $noiDungFile->NGAYKD_HIV;
        $noiLayMauXetNghiem = (string) $noiDungFile->NOI_LAY_MAU_XN;
        $noiXetNghiemKhangDinh = (string) $noiDungFile->NOI_XN_KD;
        $noiBatDauDieuTriThuocARV = (string) $noiDungFile->NOI_BDDT_ARV;
        $batDauDieuTriARV = (int) $noiDungFile->BDDT_ARV;
        $maPhacDoDieuTriKhiBatDau = (string) $noiDungFile->MA_PHAC_DO_DIEU_TRI_BD;
        $maBacPhacDoDieuTriKhiBatDau = (int) $noiDungFile->MA_BAC_PHAC_DO_BD;
        $maLyDoDieuTri = (int) $noiDungFile->MA_LYDO_DTRI;
        $loaiDieuTriLao = (int) $noiDungFile->LOAI_DTRI_LAO;
        $sangLocLao = (int) $noiDungFile->SANG_LOC_LAO;
        $phacDoDieuTriLao = (int) $noiDungFile->PHACDO_DTRI_LAO;
        $ngayBatDauDieuTriLao = (int) $noiDungFile->NGAYBD_DTRI_LAO;
        $ngayKetThucDieuTriLao = (int) $noiDungFile->NGAYKT_DTRI_LAO;
        $ketQuaDieuTriLao = (int) $noiDungFile->KQ_DTRI_LAO;
        $maLyDoXetNghiemDoTaiLuongViRut = (int) $noiDungFile->MA_LYDO_XNTL_VR;
        $ngayXetNghiemTaiLuongViRut = (int) $noiDungFile->NGAY_XN_TLVR;
        $ketQuaXetNghiemTaiLuongViRut = (int) $noiDungFile->KQ_XNTL_VR;
        $ngayKetQuaXetNghiemTaiLuongViRut = (int) $noiDungFile->NGAY_KQ_XN_TLVR;
        $maLoaiBenhNhan = (int) $noiDungFile->MA_LOAI_BN;
        $giaiDoanLamSang = (int) $noiDungFile->GIAI_DOAN_LAM_SANG;
        $nhomDoiTuong = (int) $noiDungFile->NHOM_DOI_TUONG;
        $maTinhTrangDoiTuongDenKham = (string) $noiDungFile->MA_TINHTRANG_DK;
        $lanXetNghiemPCR = (int) $noiDungFile->LAN_XN_PCR;
        $ngayXetNghiemPCR = (int) $noiDungFile->NGAY_XN_PCR;
        $ngayKetQuaXetNghiemPCR = (int) $noiDungFile->NGAY_KQ_XN_PCR;
        $maKetQuaXetNghiemPCR = (int) $noiDungFile->MA_KQ_XN_PCR;
        $ngayNhanThongTinMangThai = (int) $noiDungFile->NGAY_NHAN_TT_MANG_THAI;
        $ngayBatDauDieuTriCTX = (int) $noiDungFile->NGAY_BAT_DAU_DT_CTX;
        $maXuTri = (int) $noiDungFile->MA_XU_TRI;
        $ngayBatDauXuTri = (int) $noiDungFile->NGAY_BAT_DAU_XU_TRI;
        $ngayKetThucXuTri = (int) $noiDungFile->NGAY_KET_THUC_XU_TRI;
        $maPhacDoDieuTri = (string) $noiDungFile->MA_PHAC_DO_DIEU_TRI;
        $maBacPhacDo = (int) $noiDungFile->MA_BAC_PHAC_DO;
        $soNgayCapThuocARV = (int) $noiDungFile->SO_NGAY_CAP_THUOC_ARV;
        $ngayChuyenPhacDo = (int) $noiDungFile->NGAY_CHUYEN_PHAC_DO;
        $lyDoChuyenPhacDo = (int) $noiDungFile->LY_DO_CHUYEN_PHAC_DO;
        $maCoSoKhamChuaBenh = (string) $noiDungFile->MA_CSKCB;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML7($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $soLuuTru = (string) $noiDungFile->SO_LUU_TRU;
        $maYTe = (string) $noiDungFile->MA_YTE;
        $maKhoaRaVien = (string) $noiDungFile->MA_KHOA_RV;
        $ngayVao = (int) $noiDungFile->NGAY_VAO;
        $ngayRa = (int) $noiDungFile->NGAY_RA;
        $maDinhChiThai = (int) $noiDungFile->MA_DINH_CHI_THAI;
        $nguyenNhanDinhChi = (string) $noiDungFile->NGUYENNHAN_DINHCHI;
        $thoigianDinhChi = (int) $noiDungFile->THOIGIAN_DINHCHI;
        $tuoiThai = (int) $noiDungFile->TUOI_THAI;
        $chanDoanRaVien = (string) $noiDungFile->CHAN_DOAN_RV;
        $phuongPhapDieuTri = (string) $noiDungFile->PP_DIEU_TRI;
        $ghiChu = (string) $noiDungFile->GHI_CHU;
        $maThuTruongDonVi = (string) $noiDungFile->MA_TTDV;
        $maBacSi = (string) $noiDungFile->MA_BS;
        $tenBacSi = (string) $noiDungFile->TEN_BS;
        $ngayChungTu = (int) $noiDungFile->NGAY_CT;
        $maCha = (string) $noiDungFile->MA_CHA;
        $maMe = (string) $noiDungFile->MA_ME;
        $maTheTam = (string) $noiDungFile->MA_THE_TAM;
        $hoTenCha = (string) $noiDungFile->HO_TEN_CHA;
        $hoTenMe = (string) $noiDungFile->HO_TEN_ME;
        $soNgayNghi = (int) $noiDungFile->SO_NGAY_NGHI;
        $ngoaiTruTuNgay = (int) $noiDungFile->NGOAITRU_TUNGAY;
        $ngoaiTruDenNgay = (int) $noiDungFile->NGOAITRU_DENNGAY;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML8($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $maLoaiKCB = (string) $noiDungFile->MA_LOAI_KCB;
        $hoTenCha = (string) $noiDungFile->HO_TEN_CHA;
        $hoTenMe = (string) $noiDungFile->HO_TEN_ME;
        $nguoiGiamHo = (string) $noiDungFile->NGUOI_GIAM_HO;
        $donVi = (string) $noiDungFile->DON_VI;
        $ngayVao = (int) $noiDungFile->NGAY_VAO;
        $ngayRa = (string) $noiDungFile->NGAY_RA;
        $chanDoanVao = (string) $noiDungFile->CHAN_DOAN_VAO;
        $chanDoanRV = (string) $noiDungFile->CHAN_DOAN_RV;
        $quaTrinhBenhLy = (string) $noiDungFile->QT_BENHLY;
        $tomTatKetQua = (string) $noiDungFile->TOMTAT_KQ;
        $phuongPhapDieuTri = (string) $noiDungFile->PP_DIEUTRI;
        $ngaySinhCon = (int) $noiDungFile->NGAY_SINHCON;
        $ngayConChet = (int) $noiDungFile->NGAY_CONCHET;
        $soConChet = (int) $noiDungFile->SO_CONCHET;
        $ketQuaDieuTri = (int) $noiDungFile->KET_QUA_DTRI;
        $ghiChu = (string) $noiDungFile->GHI_CHU;
        $maThuTruongDonVi = (string) $noiDungFile->MA_TTDV;
        $ngayChungTu = (int) $noiDungFile->NGAY_CT;
        $maTheTam = (string) $noiDungFile->MA_THE_TAM;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML9($noiDungFile)
    {
        foreach ($noiDungFile as $danhSachChiTietGiayChungSinh) {
            foreach ($danhSachChiTietGiayChungSinh as $chiTietGiayChungSinh) {
                $maLienKet = (string) $chiTietGiayChungSinh->MA_LK;
                $maBHXHNguoiNuoiDuong = (int) $chiTietGiayChungSinh->MA_BHXH_NND;
                $maTheBHYTNguoiNuoiDuong = (string) $chiTietGiayChungSinh->MA_THE_NND;
                $hoTenNguoiNuoiDuong = (string) $chiTietGiayChungSinh->HO_TEN_NND;
                $ngaySinhNguoiNuoiDuong = (int) $chiTietGiayChungSinh->NGAYSINH_NND;
                $maDanTocNguoiNuoiDuong = (int) $chiTietGiayChungSinh->MA_DANTOC_NND;
                $soCCCDNguoiNuoiDuong = (string) $chiTietGiayChungSinh->SO_CCCD_NND;
                $ngayCapCCCDNguoiNuoiDuong = (int) $chiTietGiayChungSinh->NGAYCAP_CCCD_NND;
                $noiCapCCCDNguoiNuoiDuong = (string) $chiTietGiayChungSinh->NOICAP_CCCD_NND;
                $noiCuTruNguoiNuoiDuong = (string) $chiTietGiayChungSinh->NOI_CU_TRU_NND;
                $maQuocTich = (int) $chiTietGiayChungSinh->MA_QUOCTICH;
                $maTinhCuTru = (string) $chiTietGiayChungSinh->MATINH_CU_TRU;
                $maHuyenCuTru = (string) $chiTietGiayChungSinh->MAHUYEN_CU_TRU;
                $maXaCuTru = (string) $chiTietGiayChungSinh->MAXA_CU_TRU;
                $hoTenCha = (string) $chiTietGiayChungSinh->HO_TEN_CHA;
                $maTheTam = (string) $chiTietGiayChungSinh->MA_THE_TAM;
                $hoTenCon = (string) $chiTietGiayChungSinh->HO_TEN_CON;
                $gioiTinhCon = (int) $chiTietGiayChungSinh->GIOI_TINH_CON;
                $soCon = (int) $chiTietGiayChungSinh->SO_CON;
                $lanSinh = (int) $chiTietGiayChungSinh->LAN_SINH;
                $soConSong = (int) $chiTietGiayChungSinh->SO_CON_SONG;
                $canNangCon = (int) $chiTietGiayChungSinh->CAN_NANG_CON;
                $ngaySinhCon = (int) $chiTietGiayChungSinh->NGAY_SINH_CON;
                $noiSinhCon = (string) $chiTietGiayChungSinh->NOI_SINH_CON;
                $tinhTrangCon = (string) $chiTietGiayChungSinh->TINH_TRANG_CON;
                $sinhConPhauThuat = (string) $chiTietGiayChungSinh->SINHCON_PHAUTHUAT;
                $sinhConDuoi32Tuan = (string) $chiTietGiayChungSinh->SINHCON_DUOI32TUAN;
                $ghiChu = (string) $chiTietGiayChungSinh->GHI_CHU;
                $nguoiDoDe = (string) $chiTietGiayChungSinh->NGUOI_DO_DE;
                $nguoiGhiPhieu = (string) $chiTietGiayChungSinh->NGUOI_GHI_PHIEU;
                $ngayChungTu = (int) $chiTietGiayChungSinh->NGAY_CT;
                $soChungTuTaiCSKCB = (string) $chiTietGiayChungSinh->SO;
                $quyenSoChungTuTaiCSKCB = (string) $chiTietGiayChungSinh->QUYEN_SO;
                $maThuTruongDonVi = (int) $chiTietGiayChungSinh->MA_TTDV;
                $duPhong = (string) $chiTietGiayChungSinh->DU_PHONG;
            }
        }
    }
    public function handleInsertDBXML10($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $soSeri = (string) $noiDungFile->SO_SERI;
        $soChungTuTaiCSKCB = (string) $noiDungFile->SO_CT;
        $soNgay = (int) $noiDungFile->SO_NGAY;
        $donVi = (string) $noiDungFile->DON_VI;
        $chanDoanRaVien = (string) $noiDungFile->CHAN_DOAN_RV;
        $tuNgay = (int) $noiDungFile->TU_NGAY;
        $denNgay = (int) $noiDungFile->DEN_NGAY;
        $maThuTruongDonVi = (int) $noiDungFile->MA_TTDV;
        $tenBS = (string) $noiDungFile->TEN_BS;
        $maBS = (string) $noiDungFile->MA_BS;
        $ngayChungTu = (int) $noiDungFile->NGAY_CT;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML11($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $soChungTuTaiCSKCB = (string) $noiDungFile->SO_CT;
        $soSeri = (string) $noiDungFile->SO_SERI;
        $soKhamChuaBenh = (string) $noiDungFile->SO_KCB;
        $donVi = (string) $noiDungFile->DON_VI;
        $maBHXH = (int) $noiDungFile->MA_BHXH;
        $maTheBHYT = (string) $noiDungFile->MA_THE_BHYT;
        $chanDoanRaVien = (string) $noiDungFile->CHAN_DOAN_RV;
        $phuongPhapDieuTri = (string) $noiDungFile->PP_DIEUTRI;
        $maDinhChiThai = (string) $noiDungFile->MA_DINH_CHI_THAI;
        $nguyenNhanDinhChi = (string) $noiDungFile->NGUYENNHAN_DINHCHI;
        $tuoiThai = (int) $noiDungFile->TUOI_THAI;
        $soNgayNghi = (int) $noiDungFile->SO_NGAY_NGHI;
        $tuNgay = (int) $noiDungFile->TU_NGAY;
        $denNgay = (int) $noiDungFile->DEN_NGAY;
        $hoTenCha = (string) $noiDungFile->HO_TEN_CHA;
        $hoTenMe = (string) $noiDungFile->HO_TEN_ME;
        $maThuTruongDonVi = (int) $noiDungFile->MA_TTDV;
        $maBacSi = (string) $noiDungFile->MA_BS;
        $ngayChungTu = (int) $noiDungFile->NGAY_CT;
        $maTheTam = (string) $noiDungFile->MA_THE_TAM;
        $mauSo = (string) $noiDungFile->MAU_SO;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML12($noiDungFile)
    {
        $nguoiChuTri = (string) $noiDungFile->NGUOI_CHU_TRI;
        $chucVu = (int) $noiDungFile->CHUC_VU;
        $ngayHop = (int) $noiDungFile->NGAY_HOP;
        $hoTen = (string) $noiDungFile->HO_TEN;
        $ngaySinh = (int) $noiDungFile->NGAY_SINH;
        $soCCCD = (string) $noiDungFile->SO_CCCD;
        $ngayCapCCCD = (int) $noiDungFile->NGAY_CAP_CCCD;
        $noiCapCCCD = (string) $noiDungFile->NOI_CAP_CCCD;
        $diaChi = (string) $noiDungFile->DIA_CHI;
        $maTinhCuTru = (string) $noiDungFile->MATINH_CU_TRU;
        $maHuyenCuTru = (string) $noiDungFile->MAHUYEN_CU_TRU;
        $maXaCuTru = (string) $noiDungFile->MAXA_CU_TRU;
        $maBHXH = (int) $noiDungFile->MA_BHXH;
        $maTheBHYT = (string) $noiDungFile->MA_THE_BHYT;
        $ngheNghiep = (string) $noiDungFile->NGHE_NGHIEP;
        $dienThoai = (string) $noiDungFile->DIEN_THOAI;
        $maDoiTuong = (string) $noiDungFile->MA_DOI_TUONG;
        $khamGiamDinh = (int) $noiDungFile->KHAM_GIAM_DINH;
        $soBienBan = (string) $noiDungFile->SO_BIEN_BAN;
        $tyLeTonThuongCoTheCu = (float) $noiDungFile->TYLE_TTCT_CU;
        $dangHuongCheDo = (string) $noiDungFile->DANG_HUONG_CHE_DO;
        $ngayChungTu = (int) $noiDungFile->NGAY_CHUNG_TU;
        $soGiayGioiThieu = (string) $noiDungFile->SO_GIAY_GIOI_THIEU;
        $ngayDeNghi = (int) $noiDungFile->NGAY_DE_NGHI;
        $maDonVi = (string) $noiDungFile->MA_DONVI;
        $gioiThieuCua = (string) $noiDungFile->GIOI_THIEU_CUA;
        $ketQuaKham = (string) $noiDungFile->KET_QUA_KHAM;
        $soVanBanCanCu = (string) $noiDungFile->SO_VAN_BAN_CAN_CU;
        $tyLeTonThuongCoTheMoi = (float) $noiDungFile->TYLE_TTCT_MOI;
        $tongTyLeTonThuongCoThe = (float) $noiDungFile->TONG_TYLE_TTCT;
        $dangKhuyetTat = (int) $noiDungFile->DANG_KHUYETTAT;
        $mucDoKhuyetTat = (int) $noiDungFile->MUC_DO_KHUYETTAT;
        $deNghi = (string) $noiDungFile->DE_NGHI;
        $duocXacDinh = (string) $noiDungFile->DUOC_XACDINH;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML13($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $soHoSo = (string) $noiDungFile->SO_HOSO;
        $soChuyenTuyen = (string) $noiDungFile->SO_CHUYENTUYEN;
        $giayChuyenTuyen = (string) $noiDungFile->GIAY_CHUYEN_TUYEN;
        $maCSKCB = (string) $noiDungFile->MA_CSKCB;
        $maNoiDi = (string) $noiDungFile->MA_NOI_DI;
        $maNoiDen = (string) $noiDungFile->MA_NOI_DEN;
        $hoTen = (string) $noiDungFile->HO_TEN;
        $ngaySinh = (int) $noiDungFile->NGAY_SINH;
        $gioiTinh = (int) $noiDungFile->GIOI_TINH;
        $maQuocTich = (string) $noiDungFile->MA_QUOCTICH;
        $maDanToc = (string) $noiDungFile->MA_DANTOC;
        $maNgheNghiep = (string) $noiDungFile->MA_NGHE_NGHIEP;
        $diaChi = (string) $noiDungFile->DIA_CHI;
        $maTheBHYT = (string) $noiDungFile->MA_THE_BHYT;
        $giaTriTheDen = (string) $noiDungFile->GT_THE_DEN;
        $ngayVao = (int) $noiDungFile->NGAY_VAO;
        $ngayVaoNoiTru = (int) $noiDungFile->NGAY_VAO_NOI_TRU;
        $ngayRa = (int) $noiDungFile->NGAY_RA;
        $dauHieuLS = (string) $noiDungFile->DAU_HIEU_LS;
        $chanDoanRaVien = (string) $noiDungFile->CHAN_DOAN_RV;
        $quaTrinhBenhLy = (string) $noiDungFile->QT_BENHLY;
        $tomTatKetQua = (string) $noiDungFile->TOMTAT_KQ;
        $phuongPhapDieuTri = (string) $noiDungFile->PP_DIEUTRI;
        $maBenhChinh = (string) $noiDungFile->MA_BENH_CHINH;
        $maBenhKemTheo = (string) $noiDungFile->MA_BENH_KT;
        $maBenhYHCT = (string) $noiDungFile->MA_BENH_YHCT;
        $tenDichVu = (string) $noiDungFile->TEN_DICH_VU;
        $tenThuoc = (string) $noiDungFile->TEN_THUOC;
        $phuongPhapDieuTri = (string) $noiDungFile->PP_DIEU_TRI;
        $maLoaiRaVien = (int) $noiDungFile->MA_LOAI_RV;
        $maLyDoChuyenTuyen = (int) $noiDungFile->MA_LYDO_CT;
        $huongDieuTri = (string) $noiDungFile->HUONG_DIEU_TRI;
        $phuongTienVanChuyen = (string) $noiDungFile->PHUONG_TIEN_VC;
        $hoTenNguoiHoTong = (string) $noiDungFile->HOTEN_NGUOI_HT;
        $chucDanhNguoiHoTong = (string) $noiDungFile->CHUCDANH_NGUOI_HT;
        $maBacSi = (string) $noiDungFile->MA_BAC_SI;
        $maThuTruongDonVi = (string) $noiDungFile->MA_TTDV;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML14($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $soGiayHenKhamLai = (string) $noiDungFile->SO_GIAYHEN_KL;
        $maCSKCB = (string) $noiDungFile->MA_CSKCB;
        $hoTen = (string) $noiDungFile->HO_TEN;
        $ngaySinh = (int) $noiDungFile->NGAY_SINH;
        $gioiTinh = (int) $noiDungFile->GIOI_TINH;
        $diaChi = (string) $noiDungFile->DIA_CHI;
        $maTheBHYT = (string) $noiDungFile->MA_THE_BHYT;
        $giaTriTheDen = (string) $noiDungFile->GT_THE_DEN;
        $ngayVao = (int) $noiDungFile->NGAY_VAO;
        $ngayVaoNoiTru = (int) $noiDungFile->NGAY_VAO_NOI_TRU;
        $ngayRa = (int) $noiDungFile->NGAY_RA;
        $ngayHenKhamLai = (int) $noiDungFile->NGAY_HEN_KL;
        $chanDoanRaVien = (string) $noiDungFile->CHAN_DOAN_RV;
        $maBenhChinh = (string) $noiDungFile->MA_BENH_CHINH;
        $maBenhKemTheo = (string) $noiDungFile->MA_BENH_KT;
        $maBenhYHCT = (string) $noiDungFile->MA_BENH_YHCT;
        $maDoiTuongKCB = (string) $noiDungFile->MA_DOITUONG_KCB;
        $maBacSi = (string) $noiDungFile->MA_BAC_SI;
        $maThuTruongDonVi = (string) $noiDungFile->MA_TTDV;
        $ngayChungTu = (int) $noiDungFile->NGAY_CT;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
    public function handleInsertDBXML15($noiDungFile)
    {
        $maLienKet = (string) $noiDungFile->MA_LK;
        $stt = (int) $noiDungFile->STT;
        $maBenhNhan = (string) $noiDungFile->MA_BN;
        $hoTen = (string) $noiDungFile->HO_TEN;
        $soCCCD = (string) $noiDungFile->SO_CCCD;
        $phanLoaiLaoViTri = (int) $noiDungFile->PHANLOAI_LAO_VITRI;
        $phanLoaiLaoTienSu = (int) $noiDungFile->PHANLOAI_LAO_TS;
        $phanLoaiLaoHIV = (int) $noiDungFile->PHANLOAI_LAO_HIV;
        $phanLoaiLaoViKhuan = (int) $noiDungFile->PHANLOAI_LAO_VK;
        $phanLoaiLaoKhangThuoc = (int) $noiDungFile->PHANLOAI_LAO_KT;
        $loaiDieuTriLao = (int) $noiDungFile->LOAI_DTRI_LAO;
        $ngayBatDauDieuTriLao = (int) $noiDungFile->NGAYBD_DTRI_LAO;
        $phacDoDieuTriLao = (int) $noiDungFile->PHACDO_DTRI_LAO;
        $ngayKetThucDieuTriLao = (int) $noiDungFile->NGAYKT_DTRI_LAO;
        $ketQuaDieuTriLao = (int) $noiDungFile->KET_QUA_DTRI_LAO;
        $maCSKCB = (string) $noiDungFile->MA_CSKCB;
        $ngayKhangDinhHIV = (int) $noiDungFile->NGAYKD_HIV;
        $batDauDieuTriARV = (int) $noiDungFile->BDDT_ARV;
        $ngayBatDauDieuTriCTX = (int) $noiDungFile->NGAY_BAT_DAU_DT_CTX;
        $duPhong = (string) $noiDungFile->DU_PHONG;
    }
}
