<!-- resources/views/layouts/a4.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    <style>
        /* Define A4 layout styles */
        
        /* Define A4 content styles */
        .a4-content {
            /* Add additional styles for content */
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
        }
        .table th, .table td {
            /* border: 1px solid #ddd; */
            padding: 8px;
            text-align: center;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .bg-gray-200 {
            background-color: #edf2f7;
        }
    </style>
</head>
<body>
    <div class="a4-layout">
        <div class="a4-content">
            @yield('content')
        </div>
    </div>
</body>
</html>
