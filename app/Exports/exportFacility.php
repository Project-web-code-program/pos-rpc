<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use DB;

class exportFacility implements FromCollection, WithHeadings, WithTitle
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
      
        $data = DB::table('fasilitas')
        ->select('fasilitas.id as id',
                'fasilitas.codeFasilitas as codeFasilitas',
                'fasilitas.fasilitasName as fasilitasName',
                'fasilitas.locationName as locationName',
                'fasilitas.capacity as capacity',
           DB::raw("CASE WHEN fasilitas.status=1 then 'Aktif' else 'Non Aktif' end as status" ),)
        ->where('fasilitas.isDeleted', '=', '0')
        ->get();
        return collect($data);
    }


    public function headings(): array
    {
       return [
         'No',
         'Kode Fasilitas',
         'Nama Fasilitas',
         'Nama Lokasi',
         'Kapasitas',
         'Status'
       ];
    }

    public function title(): string
    {
        return 'Facility';
    }

}
