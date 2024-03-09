
@props(['WaktuPermohonan', 'valueBarcode1'  , 'valueBarcode2', 'valueName1','valueName2','valueKeputusan','waktuPenyetujuan','valueBarcode3', 'valueName3'])
<div style="display: flex; justify-content: center; margin-top: 5rem;">
    <table style="width: 100%; margin-left: 2.5rem;">
        <tbody>
            <tr>
                <td style="padding: 0.5rem 1rem 0.5rem 0; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: right; margin-bottom: 0.5rem;">{{ $WaktuPermohonan }}</h2>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 1rem 0.5rem 0; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: center; margin-bottom: 1rem;">Atasan Pemohon</h2>
                </td>
                <td style="padding: 0.5rem 0 0.5rem 1rem; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: center; margin-bottom: 1rem;">Pemohon</h2>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 1rem 0.5rem 0; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: center; margin-bottom: 1rem;">{{ $valueBarcode1 }}</h2>
                </td>
                <td style="padding: 0.5rem 0 0.5rem 1rem; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: center; margin-bottom: 1rem;">{{ $valueBarcode2 }}</h2>
                </td>
            </tr>
            <tr>
                <td style="padding: 0.5rem 1rem 0.5rem 0; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: center; margin-bottom: 1rem;">{{ $valueName1 }}</h2>
                </td>
                <td style="padding: 0.5rem 0 0.5rem 1rem; width: 50%;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; text-align: center; margin-bottom: 1rem;">{{ $valueName2 }}</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0.5rem 0; text-align: center;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal;">Permohonan Hak Akses ini : {{ $valueKeputusan }}</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0.5rem 0; text-align: center;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; margin-bottom: 1rem;">{{ $waktuPenyetujuan }}</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0.5rem 0; text-align: center;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal; margin-bottom: 1rem;">Kepala Divisi</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0.5rem 0; text-align: center;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal;">{{ $valueBarcode3 }}</h2>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 0.5rem 0; text-align: center;">
                    <h2 style="font-family: 'Book Antique'; font-size: 12pt; font-weight: normal;">{{ $valueName3 }}</h2>
                </td>
            </tr>
        </tbody>
    </table>
</div>
