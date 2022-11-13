<?php

namespace App\Http\Controllers;

use App\Exports\exportFacility;
use DB;
use File;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Validator;

class FacilityController extends Controller
{

    public function createFacility(Request $request)
    {
        DB::beginTransaction();

        try
        {


           $request->validate(['locationName' => 'required',]);
           
            
            $checkIfDataExits = DB::table('facility')
                ->where([['locationName', '=', $request->input('locationName')],
                    ['isDeleted', '=', '0']])
                ->first();

            if ($checkIfDataExits === null) {

                DB::table('facility')->insert(['locationName' => $request->input('locationName'),
                    'introduction' => $request->input('introduction'),
                    'description' => $request->input('description'),
                    'isDeleted' => 0,
                    'created_at' => now()]);
            }

            if ($request->unit) {

                $arraunit = json_decode($request->unit, true);

                if (count($arraunit) != 0) {

                    foreach ($arraunit as $val) {

                     if ( strlen($val['unitName']) > 25){

                            return response()->json([
                                'result' => 'Error',
                                'message' => 'Unit name max length 25 digit',
                            ]);

                     }else if (strlen($val['notes']) > 300){

                        return response()->json([
                            'result' => 'Error',
                            'message' => 'notes max length 300 digit',
                        ]);    

                     }else if ($val['status'] == ""){

                        return response()->json([
                            'result' => 'Error',
                            'message' => 'Status unit required, please input status',
                        ]);    

                     }else if ($val['capacity'] == ""){

                        return response()->json([
                            'result' => 'Error',
                            'message' => 'Capacity unit required, please input capacity',
                        ]);    

                     }else if ($val['amount'] == ""){

                        return response()->json([
                            'result' => 'Error',
                            'message' => 'Amount unit required, please input amount',
                        ]);    

                     }else{

                        $checkIfFacilityExits = DB::table('facility_unit')
                        ->where([['locationName', '=', $request->input('locationName')],
                            ['unitName', '=', $val['unitName']],
                            ['isDeleted', '=', '0']])
                        ->first();

                            if ($checkIfFacilityExits === null) {

                                DB::table('facility_unit')->insert(['locationName' => $request->input('locationName'),
                                    'unitName' => $val['unitName'],
                                    'status' => $val['status'],
                                    'capacity' => $val['capacity'],
                                    'amount' => $val['amount'],
                                    'notes' => $val['notes'],
                                    'isDeleted' => 0,
                                    'created_at' => now(),
                                ]);

                            } else {

                                return response()->json([
                                    'result' => 'Failed',
                                    'message' => "Unit name with spesific location already exists, please try different name",
                                ]);

                            }

                        }

                    }
                }
            }

            if ($request->hasfile('images')) {

                $files[] = $request->file('images');
                $json_array = json_decode($request->imagesName, true);
                $int = 0;

                if (count($files) != 0) {

                    foreach ($files as $file) {

                        foreach ($file as $fil) {

                            $name = $fil->hashName();
                            $fil->move(public_path() . '/FacilityImages/', $name);

                            $fileName = "/FacilityImages/" . $name;

                            DB::table('facility_images')
                                ->insert(['locationName' => $request->input('locationName'),
                                    'unitName' => $json_array[$int]['unitName'],
                                    'labelName' => $json_array[$int]['name'],
                                    'realImageName' => $fil->getClientOriginalName(),
                                    'imageName' => $name,
                                    'imagePath' => $fileName,
                                    'isDeleted' => 0,
                                    'created_at' => now(),
                                ]);

                            $int = $int + 1;
                        }
                    }
                }

            }

            DB::commit();

            return response()->json([
                'result' => 'Success',
                'message' => 'Successfully inserted new facility',
            ]);

        } catch (Exception $e) {

            DB::rollback();

            return response()->json([
                'result' => 'failed',
                'message' => $e,
            ]);

        }

    }

    public function deleteFacility(Request $request)
    {
        DB::beginTransaction();

        $request->validate(['locationName' => 'required']);

        try
        {

            foreach ($request->locationName as $val) {

                DB::table('facility')
                    ->where('locationName', '=', $val)
                    ->update(['isDeleted' => 1,
                        'updated_at' => now()]);

                DB::table('facility_unit')
                    ->where('locationName', '=', $val)
                    ->update(['isDeleted' => 1,
                        'updated_at' => now()]);

                DB::table('facility_images')
                    ->where('locationName', '=', $val)
                    ->update(['isDeleted' => 1,
                        'updated_at' => now()]);

                DB::commit();

            }

            return response()->json([
                'result' => 'success',
                'message' => 'Successfully deleted facility',
            ]);

        } catch (Exception $e) {

            DB::rollback();

            return response()->json([
                'result' => 'failed',
                'message' => $e,
            ]);

        }

    }

    public function facilityDetail(Request $request)
    {

        $request->validate(['locationName' => 'required|max:10000']);
        $locationName = $request->input('locationName');

        $checkIfValueExits = DB::table('facility')
            ->where([['facility.locationName', '=', $locationName],
                ['facility.isDeleted', '=', '0']])
            ->first();

        if ($checkIfValueExits === null) {

            return response()->json([
                'result' => 'Failed',
                'message' => "Data not exists, please try another location name",
            ]);

        } else {

            $facility = DB::table('facility')
                ->select('facility.locationName as locationName',
                    'facility.introduction as introduction',
                    'facility.description as description', )
                ->where([['facility.locationName', '=', $locationName],
                    ['facility.isDeleted', '=', '0']])
                ->first();

            $fasilitas_unit = DB::table('facility_unit')
                ->select('facility_unit.unitName as unitName',
                    'facility_unit.status as status',
                    'facility_unit.capacity as capacity',
                    'facility_unit.amount as amount',
                    'facility_unit.notes as notes', )
                ->where([['facility_unit.locationName', '=', $locationName],
                    ['facility_unit.isDeleted', '=', '0']])
                ->get();

            $facility->unit = $fasilitas_unit;

            $fasilitas_images = DB::table('facility_images')
                ->select('facility_images.id as id',
                    'facility_images.locationName as locationName',
                    'facility_images.unitName as unitName',
                    'facility_images.labelName as labelName',
                    'facility_images.imagePath as imagePath', )
                ->where([['facility_images.locationName', '=', $locationName],
                    ['facility_images.isDeleted', '=', '0']])
                ->get();

            $facility->images = $fasilitas_images;

            return response()->json($facility, 200);
        }

    }

    public function searchImageFacility(Request $request)
    {

        $request->validate(['locationName' => 'required|max:10000']);

        $checkIfValueExits = DB::table('facility_images')
            ->where([['facility_images.locationName', '=', $request->input('locationName')],
                ['facility_images.isDeleted', '=', '0']])
            ->first();

        if ($checkIfValueExits === null) {

            return response()->json([
                'result' => 'Failed',
                'message' => "Data not exists",
            ]);

        } else {

            $images = DB::table('facility_images')
                ->select('facility_images.id as id',
                    'facility_images.locationName as locationName',
                    'facility_images.unitName as unitName',
                    'facility_images.labelName as labelName',
                    'facility_images.imagePath as imagePath', )
                ->where([['facility_images.locationName', '=', $request->input('locationName')],
                    ['facility_images.isDeleted', '=', '0']]);

            if ($request->name) {
                $res = $this->SearchImages($request);

                if ($res) {
                    $images = $images->where($res, 'like', '%' . $request->name . '%');
                } else {
                    $images = [];
                    return response()->json($images, 200);
                }
            }
            $images = $images->orderBy('facility_images.created_at', 'desc');
            $images = $images->get();
            return response()->json(['images' => $images], 200);

        }

    }

    private function SearchImages($request)
    {

        $data = DB::table('facility_images')
            ->select('facility_images.labelName as labelName',
                'facility_images.realImageName as realImageName',
                'facility_images.imageName as imageName',
                'facility_images.imagePath as imagePath', )
            ->where([['facility_images.locationName', '=', $request->locationName],
                ['facility_images.isDeleted', '=', '0']]);

        if ($request->name) {
            $data = $data->where('facility_images.labelName', 'like', '%' . $request->name . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column = 'facility_images.labelName';
            return $temp_column;
        }

    }

    public function updateFacility(Request $request)
    {
        DB::beginTransaction();

        $request->validate(['locationName' => 'required',]);

        try
        {

            DB::table('facility')
                ->where('locationName', '=', $request->input('locationName'))
                ->update(['introduction' => $request->input('introduction'),
                            'description' => $request->input('description'),
                            'updated_at' => now(),
                ]);

            if ($request->unit) {

                if (count($request->unit) != 0) {

                    foreach ($request->unit as $val) {

                        if (isset($val['command'])) {

                            if ($val['command'] == "del") {

                                DB::table('facility_unit')
                                    ->where([['locationName', '=', $request->input('locationName')],
                                        ['unitName', '=', $val['unitName']],
                                        ['isDeleted', '=', '0']])
                                    ->update(['unitName' => $val['unitName'],
                                        'isDeleted' => 1,
                                        'updated_at' => now(),
                                    ]);

                                DB::table('facility_images')
                                    ->where([['locationName', '=', $request->input('locationName')],
                                        ['unitName', '=', $val['unitName']],
                                        ['isDeleted', '=', '0']])
                                    ->update(['isDeleted' => 1,
                                        'updated_at' => now()]);

                            } else {

                                $checkIfDataExits = DB::table('facility_unit')
                                    ->where([['locationName', '=', $request->input('locationName')],
                                        ['unitName', '=', $val['unitName']],
                                        ['isDeleted', '=', '0']])
                                    ->first();

                                if ($checkIfDataExits != null) {

                                    return response()->json([
                                        'result' => 'Failed',
                                        'message' => 'Unit name : ' . $val['unitName'] . ', for this location name : ' . $request->input('locationName') . ' already exists, please try different unit name',
                                    ]);

                                } else {


                                if ( strlen($val['unitName']) > 25){

                                        return response()->json([
                                            'result' => 'Error',
                                            'message' => 'Unit name max length 25 digit',
                                        ]);
            
                                 }else if (strlen($val['notes']) > 300){
            
                                    return response()->json([
                                        'result' => 'Error',
                                        'message' => 'notes max length 300 digit',
                                    ]);    
            
                                 }else if ($val['status'] == ""){
            
                                    return response()->json([
                                        'result' => 'Error',
                                        'message' => 'Status unit required, please input status',
                                    ]);    
            
                                 }else if ($val['capacity'] == ""){
            
                                    return response()->json([
                                        'result' => 'Error',
                                        'message' => 'Capacity unit required, please input capacity',
                                    ]);    
            
                                 }else if ($val['amount'] == ""){
            
                                    return response()->json([
                                        'result' => 'Error',
                                        'message' => 'Amount unit required, please input amount',
                                    ]);    
            
                                 }else{


                                    DB::table('facility_unit')
                                        ->insert(['locationName' => $request->input('locationName'),
                                            'unitName' => $val['unitName'],
                                            'status' => $val['status'],
                                            'capacity' => $val['capacity'],
                                            'amount' => $val['amount'],
                                            'notes' => $val['notes'],
                                            'isDeleted' => 0,
                                            'created_at' => now(),
                                        ]);

                                    }

                                }

                            }

                        } else {
                            
                            if ( strlen($val['unitName']) > 25){

                                return response()->json([
                                    'result' => 'Error',
                                    'message' => 'Unit name max length 25 digit',
                                ]);
    
                         }else if (strlen($val['notes']) > 300){
    
                            return response()->json([
                                'result' => 'Error',
                                'message' => 'notes max length 300 digit',
                            ]);    
    
                         }else if ($val['status'] == ""){
    
                            return response()->json([
                                'result' => 'Error',
                                'message' => 'Status unit required, please input status',
                            ]);    
    
                         }else if ($val['capacity'] == ""){
    
                            return response()->json([
                                'result' => 'Error',
                                'message' => 'Capacity unit required, please input capacity',
                            ]);    
    
                         }else if ($val['amount'] == ""){
    
                            return response()->json([
                                'result' => 'Error',
                                'message' => 'Amount unit required, please input amount',
                            ]);    
    
                         }else{

                                    DB::table('facility_unit')
                                        ->where([['locationName', '=', $request->input('locationName')],
                                            ['unitName', '=', $val['unitName']]])
                                        ->update(['unitName' => $val['unitName'],
                                            'capacity' => $val['capacity'],
                                            'amount' => $val['amount'],
                                            'status' => $val['status'],
                                            'notes' => $val['notes'],
                                            'updated_at' => now(),
                                        ]);

                             }

                        }

                    }

                }
            }

            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'successfuly update facility',
            ]);

        } catch (Exception $e) {

            DB::rollback();

            return response()->json([
                'result' => 'failed',
                'message' => $e,
            ]);

        }

    }

    public function uploadImageFacility(Request $request)
    {

        try {

            try {

                $json_array = json_decode($request->imagesName, true);
                $int = 0;

                if (count($json_array) != 0) {

                    foreach ($json_array as $val) {

                        if ($val['id'] != "") {

                            if ($val['status'] == "del") {

                                $find_image = DB::table('facility_images')
                                    ->select('facility_images.imageName',
                                        'facility_images.imagePath')
                                    ->where('id', '=', $val['id'])
                                    ->where('unitName', '=', $request->input('unitName'))
                                    ->first();

                                if ($find_image) {

                                    if (file_exists(public_path() . $find_image->imagePath)) {

                                        File::delete(public_path() . $find_image->imagePath);

                                        DB::table('facility_images')->where([['unitName', '=', $request->input('unitName')],
                                            ['id', '=', $val['id']]])->delete();
                                    }

                                }

                            } else {

                                $find_image = DB::table('facility_images')
                                    ->select('facility_images.imageName',
                                        'facility_images.imagePath')
                                    ->where('id', '=', $val['id'])
                                    ->where('unitName', '=', $request->input('unitName'))
                                    ->first();

                                if ($find_image) {

                                    DB::table('facility_images')
                                        ->where([['unitName', '=', $request->input('unitName')],
                                            ['id', '=', $val['id']]])
                                        ->update(['labelName' => $val['name'],
                                            'updated_at' => now(),
                                        ]);

                                }

                            }

                        } else {

                            $files[] = $request->file('images');

                            foreach ($files as $file) {

                                foreach ($file as $fil) {

                                    $name = $fil->hashName();
                                    $fil->move(public_path() . '/FacilityImages/', $name);

                                    $fileName = "/FacilityImages/" . $name;

                                    DB::table('facility_images')
                                        ->insert(['locationName' => $request->input('locationName'),
                                            'unitName' => $request->input('unitName'),
                                            'labelName' => $val['name'],
                                            'realImageName' => $fil->getClientOriginalName(),
                                            'imageName' => $name,
                                            'imagePath' => $fileName,
                                            'isDeleted' => 0,
                                            'created_at' => now(),
                                        ]);
                                }

                            }
                        }

                    }

                }

                DB::commit();

                return response()->json([
                    'result' => 'success',
                    'message' => 'successfuly update image facility',
                ]);

            } catch (Exception $e) {

                DB::rollback();

                return response()->json([
                    'result' => 'failed',
                    'message' => $e,
                ]);

            }

            DB::commit();

            return response()->json([
                'result' => 'success',
                'message' => 'successfuly update image facility',
            ]);

        } catch (Exception $e) {

            DB::rollback();

            return response()->json([
                'result' => 'failed',
                'message' => $e,
            ]);

        }

    }

    public function facilityMenuHeader(Request $request)
    {

        $defaultRowPerPage = 5;

        $data = DB::table('location')
            ->leftjoin(DB::raw('(select * from facility where isDeleted=0) as facility'),
                function ($join) {
                    $join->on('facility.locationName', '=', 'location.locationName');
                })
            ->leftjoin(DB::raw('(select * from facility_unit where isDeleted=0) as facility_unit'),
                function ($join) {
                    $join->on('facility_unit.locationName', '=', 'facility.locationName');
                })
            ->select('location.id as id',
                'location.codeLocation as codeLocation',
                'location.locationName as locationName',
                'location.created_at as createdAt',
                DB::raw("IFNULL (SUM(facility_unit.capacity),0) as capacityUsage"),
                DB::raw("IFNULL (count(DISTINCT(facility.locationName)),0) as facilityVariation"),
                DB::raw("IFNULL (count(facility_unit.unitName),0) as unitTotal"))
            ->where([['location.isDeleted', '=', '0']])
            ->groupBy('location.locationName', 'location.codeLocation', 'location.id', 'location.created_at');

        if ($request->search || $request->search == 0) {

            $res = $this->Search($request);

            if (str_contains($res, "location.locationName")) {

                $data = $data->having($res, 'like', '%' . $request->search . '%');

            } else if (str_contains($res, "facility_unit.capacity")) {

                $data = $data->having(DB::raw('IFNULL(SUM(facility_unit.capacity),0)'), '=', $request->search);

            } else if (str_contains($res, "facility_unit.unitName")) {

                $data = $data->having(DB::raw('IFNULL(count(facility_unit.unitName),0)'), '=', $request->search);

            } else if (str_contains($res, "facility.locationName")) {

                $data = $data->having(DB::raw('IFNULL(count(DISTINCT(facility.locationName)),0)'), '=', $request->search);

            } else {

                $data = [];
                return response()->json(['totalPagination' => 0,
                    'data' => $data], 200);
            }
        }

        if ($request->orderColumn && $request->orderValue) {
            $data = $data->orderBy($request->orderColumn, $request->orderValue);
        }

        $data = $data->orderBy('location.created_at', 'desc');

        if ($request->rowPerPage > 0) {
            $defaultRowPerPage = $request->rowPerPage;
        }

        $goToPage = $request->goToPage;

        $offset = ($goToPage - 1) * $defaultRowPerPage;

        $count_data = $data->count();
        $count_result = $count_data - $offset;

        if ($count_result < 0) {
            $data = $data->offset(0)->limit($defaultRowPerPage)->get();
        } else {
            $data = $data->offset($offset)->limit($defaultRowPerPage)->get();
        }

        $total_paging = $count_data / $defaultRowPerPage;
        return response()->json(['totalPagination' => ceil($total_paging), 'data' => $data], 200);

    }

    private function Search($request)
    {

        $data = DB::table('location')
            ->leftjoin(DB::raw('(select * from facility where isDeleted=0) as facility'),
                function ($join) {
                    $join->on('facility.locationName', '=', 'location.locationName');
                })
            ->leftjoin(DB::raw('(select * from facility_unit where isDeleted=0) as facility_unit'),
                function ($join) {
                    $join->on('facility_unit.locationName', '=', 'facility.locationName');
                })
            ->select('location.id as id',
                'location.codeLocation as codeLocation',
                'location.locationName as locationName',
                'location.created_at as createdAt',
                DB::raw("IFNULL (SUM(facility_unit.capacity),0) as capacityUsage"),
                DB::raw("IFNULL (count(DISTINCT(facility.locationName)),0) as facilityVariation"),
                DB::raw("IFNULL (count(facility_unit.unitName),0) as unitTotal"))
            ->where([['location.isDeleted', '=', '0']])
            ->groupBy('location.locationName', 'location.codeLocation', 'location.id', 'location.created_at');

        if ($request->search || $request->search == 0) {
            $data = $data->where('location.locationName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column = 'location.locationName';
            return $temp_column;
        }

        $data = DB::table('location')
            ->leftjoin(DB::raw('(select * from facility where isDeleted=0) as facility'),
                function ($join) {
                    $join->on('facility.locationName', '=', 'location.locationName');
                })
            ->leftjoin(DB::raw('(select * from facility_unit where isDeleted=0) as facility_unit'),
                function ($join) {
                    $join->on('facility_unit.locationName', '=', 'facility.locationName');
                })
            ->select('location.id as id',
                'location.codeLocation as codeLocation',
                'location.locationName as locationName',
                'location.created_at as createdAt',
                DB::raw("IFNULL (SUM(facility_unit.capacity),0) as capacityUsage"),
                DB::raw("IFNULL (count(DISTINCT(facility.locationName)),0) as facilityVariation"),
                DB::raw("IFNULL (count(facility_unit.unitName),0) as unitTotal"))
            ->where([['location.isDeleted', '=', '0']])
            ->groupBy('location.locationName', 'location.codeLocation', 'location.id', 'location.created_at');

        if ($request->search || $request->search == 0) {
            $data = $data->having(DB::raw('IFNULL (SUM(facility_unit.capacity),0)'), '=', $request->search);
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column = 'IFNULL(SUM(facility_unit.capacity),0)';
            return $temp_column;
        }

        $data = DB::table('location')
            ->leftjoin(DB::raw('(select * from facility where isDeleted=0) as facility'),
                function ($join) {
                    $join->on('facility.locationName', '=', 'location.locationName');
                })
            ->leftjoin(DB::raw('(select * from facility_unit where isDeleted=0) as facility_unit'),
                function ($join) {
                    $join->on('facility_unit.locationName', '=', 'facility.locationName');
                })
            ->select('location.id as id',
                'location.codeLocation as codeLocation',
                'location.locationName as locationName',
                'location.created_at as createdAt',
                DB::raw("IFNULL (SUM(facility_unit.capacity),0) as capacityUsage"),
                DB::raw("IFNULL (count(DISTINCT(facility.locationName)),0) as facilityVariation"),
                DB::raw("IFNULL (count(facility_unit.unitName),0) as unitTotal"))
            ->where([['location.isDeleted', '=', '0']])
            ->groupBy('location.locationName', 'location.codeLocation', 'location.id', 'location.created_at');

        if ($request->search || $request->search == 0) {
            $data = $data->having(DB::raw('IFNULL(count(DISTINCT(facility.locationName)),0)'), '=', $request->search);
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column = 'IFNULL(count(DISTINCT(facility.locationName)),0)';
            return $temp_column;
        }

        $data = DB::table('location')
            ->leftjoin(DB::raw('(select * from facility where isDeleted=0) as facility'),
                function ($join) {
                    $join->on('facility.locationName', '=', 'location.locationName');
                })
            ->leftjoin(DB::raw('(select * from facility_unit where isDeleted=0) as facility_unit'),
                function ($join) {
                    $join->on('facility_unit.locationName', '=', 'facility.locationName');
                })
            ->select('location.id as id',
                'location.codeLocation as codeLocation',
                'location.locationName as locationName',
                'location.created_at as createdAt',
                DB::raw("IFNULL (SUM(facility_unit.capacity),0) as capacityUsage"),
                DB::raw("IFNULL (count(DISTINCT(facility.locationName)),0) as facilityVariation"),
                DB::raw("IFNULL (count(facility_unit.unitName),0) as unitTotal"))
            ->where([['location.isDeleted', '=', '0']])
            ->groupBy('location.locationName', 'location.codeLocation', 'location.id', 'location.created_at');

        if ($request->search || $request->search == 0) {
            $data = $data->having(DB::raw('IFNULL(count(facility_unit.unitName),0)'), '=', $request->search);
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column = 'IFNULL(count(facility_unit.unitName),0)';
            return $temp_column;
        }

    }

    public function facilityExport(Request $request)
    {

        try
        {
            return Excel::download(new exportFacility, 'Facility.xlsx');

        } catch (Exception $e) {

            DB::rollback();

            return response()->json([
                'result' => 'Failed',
                'message' => $e,
            ]);

        }

    }

    public function facilityLocation(Request $request)
    {

        try
        {

            $getLocationFasilitas = DB::table('location')
                ->select('location.id as id',
                    'location.locationName as locationName', )
                ->where('location.isDeleted', '=', '0')
                ->get();

            return response()->json($getLocationFasilitas, 200);

        } catch (Exception $e) {

            return response()->json([
                'result' => 'Failed',
                'message' => $e,
            ]);
        }

    }

}
