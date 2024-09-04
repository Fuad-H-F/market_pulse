<html>
<head>
    <style>
        table, td, th {
            border: 1px solid black;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>
<body>
<table>
    <thead>
    <tr>
        <th scope="col">Serial No.</th>
        <th scope="col">User Name</th>
        <th scope="col">Report Date</th>
        <th scope="col">Time Stamp</th>
        <th scope="col">Area</th>
        <th scope="col">GPS</th>
        <th scope="col">Picture</th>
        <th scope="col">Report</th>
    </tr>
    </thead>
    <tbody>
    @foreach($locations as $location)
        <tr>
            <td style="width: 90px" scope="row">
                {{$loop->index+1}}
            </td>
            <td>
                {{\App\Models\UserManager::where('UserID',$location->UserID)->first()->UserName}}
            </td>
            <td>
                {{\Carbon\Carbon::parse($location->AttendanceTime)->format('d/m/Y')}}
            </td>
            <td>
                {{\Carbon\Carbon::parse($location->AttendanceTime)->format('g:i A')}}
            </td>
            <td>
                {{$location->Area}}
            </td>
            <td>
                {{$location->Latitude.', '.$location->Longitude}}
            </td>
            <td style="width: 120px">
                <img style="width: 100%" src="{{'https://apps.acibd.com/apps/market_pulse/'.$location->AttendanceImage}}">
            </td>
            <td>
                {{$location->Comment}}
            </td>
        </tr>

    @endforeach
    </tbody>
</table>
</body>
</html>
