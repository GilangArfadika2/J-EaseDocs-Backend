<!-- resources/views/components/tabel.blade.php -->
@props(['header', 'data'])

<div class="overflow-x-auto mt-4">
    <table class="table-auto w-full border-collapse">
        <thead>
            <tr class="bg-gray-200">
                @foreach ($header as $headerItem)
                    <th class="px-4 py-2 text-center">{{ $headerItem }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
                <tr class="border-t">
                    @foreach ($row as $cell)
                        <td class="border px-4 py-2 text-center">{{ $cell }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
