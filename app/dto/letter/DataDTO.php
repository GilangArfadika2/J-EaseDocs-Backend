<?php
namespace App\DTO\Letter;

class HeaderDTO
{
    public $header;

    public function __construct($header)
    {
        $this->header = $header;
    }
}

class IsianDTO
{
    public $label;
    public $value;

    public function __construct($label, $value)
    {
        $this->label = $label;
        $this->value = $value;
    }
}

class DeskripsiDTO
{
    public $deskripsi;

    public function __construct($deskripsi)
    {
        $this->deskripsi = $deskripsi;
    }
}

class TabelDTO
{
    public $kolomTabel;
    public $barisTabel;

    public function __construct($kolomTabel, $barisTabel)
    {
        $this->kolomTabel = $kolomTabel;
        $this->barisTabel = $barisTabel;
    }
}

class TabelTandaTanganDTO
{
    public $waktuPermohonan;
    public $waktuPenyetujuan;
    public $valueName1;
    public $valueName2;
    public $valueName3;
    public $valueKeputusan;
    public $valueBarcode1;
    public $valueBarcode2;
    public $valueBarcode3;

    public function __construct($data)
    {
        $this->waktuPermohonan = $data['waktuPermohonan'];
        $this->waktuPenyetujuan = $data['waktuPenyetujuan'];
        $this->valueName1 = $data['valueName1'];
        $this->valueName2 = $data['valueName2'];
        $this->valueName3 = $data['valueName3'];
        $this->valueKeputusan = $data['valueKeputusan'];
        $this->valueBarcode1 = $data['valueBarcode1'];
        $this->valueBarcode2 = $data['valueBarcode2'];
        $this->valueBarcode3 = $data['valueBarcode3'];
    }
}