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
    <p><strong>Danh sách dịch vụ khám đã đăng ký:</strong></p>

    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên dịch vụ</th>
                <th>Phòng thực hiện</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sereServList as $index => $dv)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dv['TDL_SERVICE_NAME'] ?? '' }}</td>
                    <td>{{ $dv['EXECUTE_ROOM_NAME'] ?? '' }}</td>
                    <td>{{ $dv['AMOUNT'] ?? '' }}</td>
                    <td>{{ $dv['PRICE'] ?? '' }}</td>
                    <!-- <td>{{ \Carbon\Carbon::parse($dv['intruction_time'] ?? '')->format('d/m/Y H:i') }}</td> -->
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
