@php
    $inTime = \Carbon\Carbon::parse($responeMos['HisTreatment']['IN_TIME'] ?? null)->format('H:i d/m/Y'); // ngày yêu cầu
    $patientName = $responeMos['HisPatientProfile']['HisPatient']['VIR_PATIENT_NAME'] ?? '';
    $sereServs = $responeMos['SereServs'] ?? [];
    $serviceReqs = $responeMos['ServiceReqs'] ?? [];
@endphp

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo đăng ký khám thành công</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid #000;
            padding: 6px;
        }
    </style>
</head>
<body>
    <p><strong>Xin chào {{ $patientName }}. Bạn đã đăng ký khám thành công. Ngày đăng ký khám: {{ $inTime }}</strong></p>

    <p><strong>Danh sách dịch vụ khám đã đăng ký:</strong></p>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên dịch vụ</th>
                <th>Số thứ tự khám</th>
                <th>Phòng thực hiện</th>
                <th>Số lượng</th>
                <th>Đơn giá viện phí (VNĐ)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sereServs as $index => $dv)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dv['TDL_SERVICE_NAME'] ?? '' }}</td>
                    <td>{{ collect($serviceReqs)->firstWhere('ID', $dv['SERVICE_REQ_ID'])['NUM_ORDER'] ?? '' }}</td>
                    <td>{{ $dv['EXECUTE_ROOM_NAME'] ?? '' }}</td>
                    <td>{{ $dv['AMOUNT'] ?? '' }}</td>
                    <td>{{ isset($dv['PRICE']) ? number_format($dv['PRICE']) : '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
