<?php

namespace App\Http\Controllers;

use App\Models\childrenMenuGroups;
use App\Models\grandChildrenMenuGroups;
use App\Models\menuGroup;
use App\Models\menuProfile;
use App\Models\menuSettings;
use Illuminate\Http\Request;
use DB;
use Validator;
use Illuminate\Support\Carbon;

class MenuManagementController extends Controller
{
    public function listMenuGroup()
    {
        $data = DB::table('menuGroups')
            ->select('id', 'groupName')
            ->where('isDeleted', '=', 0)
            ->orderBy('orderMenu', 'asc')
            ->get();

        return responseList($data);
    }

    public function listChildrenMenu(Request $request)
    {
        $data = DB::table('grandChildrenMenuGroups')
            ->select('id', 'menuName')
            ->where('childrenId', '=', $request->id)
            ->where('isDeleted', '=', 0)
            ->orderBy('orderMenu', 'asc')
            ->get();

        return responseList($data);
    }

    public function indexMenuGroup(Request $request)
    {
        $itemPerPage = $request->rowPerPage;

        $page = $request->goToPage;

        $data = DB::table('menuGroups as mg')
            ->join('users as u', 'mg.userId', 'u.id')
            ->select(
                'mg.id',
                'mg.groupName',
                'mg.orderMenu',
                'u.firstName as createdBy',
                DB::raw("DATE_FORMAT(mg.created_at, '%d/%m/%Y %H:%i:%s') as createdAt")
            )
            ->where('mg.isDeleted', '=', 0);

        if ($request->search) {
            $res = $this->SearchMenuGroup($request);
            if ($res) {
                $data = $data->where($res[0], 'like', '%' . $request->search . '%');

                for ($i = 1; $i < count($res); $i++) {
                    $data = $data->orWhere($res[$i], 'like', '%' . $request->search . '%');
                }
            } else {
                $data = [];
                return response()->json([
                    'totalPagination' => 0,
                    'data' => $data
                ], 200);
            }
        }

        if ($request->orderValue) {
            $data = $data->orderBy($request->orderColumn, $request->orderValue);
        }

        $data = $data->orderBy('mg.updated_at', 'desc');

        $offset = ($page - 1) * $itemPerPage;

        $count_data = $data->count();
        $count_result = $count_data - $offset;

        if ($count_result < 0) {
            $data = $data->offset(0)->limit($itemPerPage)->get();
        } else {
            $data = $data->offset($offset)->limit($itemPerPage)->get();
        }

        $totalPaging = $count_data / $itemPerPage;

        return response()->json([
            'totalPagination' => ceil($totalPaging),
            'data' => $data
        ], 200);
    }

    private function SearchMenuGroup($request)
    {
        $temp_column = null;

        $data = DB::table('menuGroups as mg')
            ->select(
                'mg.groupName',
            )
            ->where('mg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('mg.groupName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'mg.groupName';
        }

        return $temp_column;
    }

    public function indexChildrenMenu(Request $request)
    {
        $itemPerPage = $request->rowPerPage;

        $page = $request->goToPage;

        $data = DB::table('childrenMenuGroups as cmg')
            ->join('users as u', 'cmg.userId', 'u.id')
            ->select(
                'cmg.id',
                'cmg.menuName',
                'cmg.identify',
                'cmg.title',
                'cmg.type',
                'cmg.icon',
                'cmg.orderMenu',
                'u.firstName as createdBy',
                DB::raw("DATE_FORMAT(cmg.created_at, '%d/%m/%Y %H:%i:%s') as createdAt")
            )
            ->where('cmg.isDeleted', '=', 0);
        if ($request->search) {
            $res = $this->SearchChildMenu($request);
            if ($res) {
                $data = $data->where($res[0], 'like', '%' . $request->search . '%');

                for ($i = 1; $i < count($res); $i++) {
                    $data = $data->orWhere($res[$i], 'like', '%' . $request->search . '%');
                }
            } else {
                $data = [];
                return response()->json([
                    'totalPagination' => 0,
                    'data' => $data
                ], 200);
            }
        }

        if ($request->orderValue) {
            $data = $data->orderBy($request->orderColumn, $request->orderValue);
        }

        $data = $data->orderBy('cmg.updated_at', 'desc');

        $offset = ($page - 1) * $itemPerPage;

        $count_data = $data->count();
        $count_result = $count_data - $offset;

        if ($count_result < 0) {
            $data = $data->offset(0)->limit($itemPerPage)->get();
        } else {
            $data = $data->offset($offset)->limit($itemPerPage)->get();
        }

        $totalPaging = $count_data / $itemPerPage;

        return response()->json([
            'totalPagination' => ceil($totalPaging),
            'data' => $data
        ], 200);
    }

    private function SearchChildMenu($request)
    {
        $temp_column = null;

        $data = DB::table('childrenMenuGroups as cmg')
            ->select(
                'cmg.menuName',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.menuName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.menuName';
        }

        $data = DB::table('childrenMenuGroups as cmg')
            ->select(
                'cmg.identify',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.identify', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.identify';
        }

        $data = DB::table('childrenMenuGroups as cmg')
            ->select(
                'cmg.title',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.title', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.title';
        }

        $data = DB::table('childrenMenuGroups as cmg')
            ->select(
                'cmg.type',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.type', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.type';
        }

        $data = DB::table('childrenMenuGroups as cmg')
            ->select(
                'cmg.icon',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.icon', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.icon';
        }

        return $temp_column;
    }

    public function indexGrandChildMenu(Request $request)
    {
        $itemPerPage = $request->rowPerPage;

        $page = $request->goToPage;

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->join('users as u', 'cmg.userId', 'u.id')
            ->select(
                'cmg.id',
                'cm.menuName as childMenuName',
                'cmg.menuName',
                'cmg.identify',
                'cmg.title',
                'cmg.type',
                'cmg.url',
                'cmg.orderMenu',
                'u.firstName as createdBy',
                DB::raw("DATE_FORMAT(cmg.created_at, '%d/%m/%Y %H:%i:%s') as createdAt")
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $res = $this->SearchGrandChild($request);
            if ($res) {
                $data = $data->where($res[0], 'like', '%' . $request->search . '%');

                for ($i = 1; $i < count($res); $i++) {
                    $data = $data->orWhere($res[$i], 'like', '%' . $request->search . '%');
                }
            } else {
                $data = [];
                return response()->json([
                    'totalPagination' => 0,
                    'data' => $data
                ], 200);
            }
        }

        if ($request->orderValue) {
            $data = $data->orderBy($request->orderColumn, $request->orderValue);
        }

        $data = $data->orderBy('cmg.updated_at', 'desc');

        $offset = ($page - 1) * $itemPerPage;

        $count_data = $data->count();
        $count_result = $count_data - $offset;

        if ($count_result < 0) {
            $data = $data->offset(0)->limit($itemPerPage)->get();
        } else {
            $data = $data->offset($offset)->limit($itemPerPage)->get();
        }

        $totalPaging = $count_data / $itemPerPage;

        return response()->json([
            'totalPagination' => ceil($totalPaging),
            'data' => $data
        ], 200);
    }

    private function SearchGrandChild($request)
    {
        $temp_column = null;

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->select(
                'cm.menuName',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cm.menuName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cm.menuName';
        }

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->select(
                'cmg.menuName',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.menuName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.menuName';
        }

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->select(
                'cmg.identify',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.identify', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.identify';
        }

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->select(
                'cmg.title',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.title', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.title';
        }

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->select(
                'cmg.type',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.type', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.type';
        }

        $data = DB::table('grandChildrenMenuGroups as cmg')
            ->join('childrenMenuGroups as cm', 'cmg.childrenId', 'cm.id')
            ->select(
                'cmg.url',
            )
            ->where('cmg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('cmg.url', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'cmg.url';
        }

        return $temp_column;
    }

    function indexMenuProfile(Request $request)
    {
        $itemPerPage = $request->rowPerPage;

        $page = $request->goToPage;

        $data = DB::table('menuProfiles as mp')
            ->join('users as u', 'mp.userId', 'u.id')
            ->select(
                'mp.id',
                'mp.title',
                'mp.url',
                'mp.icon',
                'u.firstName as createdBy',
                DB::raw("DATE_FORMAT(mp.created_at, '%d/%m/%Y %H:%i:%s') as createdAt")
            )
            ->where('mp.isDeleted', '=', 0);

        if ($request->search) {
            $res = $this->SearchMenuProfile($request);
            if ($res) {
                $data = $data->where($res[0], 'like', '%' . $request->search . '%');

                for ($i = 1; $i < count($res); $i++) {
                    $data = $data->orWhere($res[$i], 'like', '%' . $request->search . '%');
                }
            } else {
                $data = [];
                return response()->json([
                    'totalPagination' => 0,
                    'data' => $data
                ], 200);
            }
        }

        if ($request->orderValue) {
            $data = $data->orderBy($request->orderColumn, $request->orderValue);
        }

        $data = $data->orderBy('mp.updated_at', 'desc');

        $offset = ($page - 1) * $itemPerPage;

        $count_data = $data->count();
        $count_result = $count_data - $offset;

        if ($count_result < 0) {
            $data = $data->offset(0)->limit($itemPerPage)->get();
        } else {
            $data = $data->offset($offset)->limit($itemPerPage)->get();
        }

        $totalPaging = $count_data / $itemPerPage;

        return response()->json([
            'totalPagination' => ceil($totalPaging),
            'data' => $data
        ], 200);
    }

    function indexMenuSetting(Request $request)
    {
        $itemPerPage = $request->rowPerPage;

        $page = $request->goToPage;

        $data = DB::table('menuSettings as mp')
            ->join('users as u', 'mp.userId', 'u.id')
            ->select(
                'mp.id',
                'mp.title',
                'mp.url',
                'mp.icon',
                'u.firstName as createdBy',
                DB::raw("DATE_FORMAT(mp.created_at, '%d/%m/%Y %H:%i:%s') as createdAt")
            )
            ->where('mp.isDeleted', '=', 0);

        if ($request->search) {
            $res = $this->SearchMenuSetting($request);
            if ($res) {
                $data = $data->where($res[0], 'like', '%' . $request->search . '%');

                for ($i = 1; $i < count($res); $i++) {
                    $data = $data->orWhere($res[$i], 'like', '%' . $request->search . '%');
                }
            } else {
                $data = [];
                return response()->json([
                    'totalPagination' => 0,
                    'data' => $data
                ], 200);
            }
        }

        if ($request->orderValue) {
            $data = $data->orderBy($request->orderColumn, $request->orderValue);
        }

        $data = $data->orderBy('mp.updated_at', 'desc');

        $offset = ($page - 1) * $itemPerPage;

        $count_data = $data->count();
        $count_result = $count_data - $offset;

        if ($count_result < 0) {
            $data = $data->offset(0)->limit($itemPerPage)->get();
        } else {
            $data = $data->offset($offset)->limit($itemPerPage)->get();
        }

        $totalPaging = $count_data / $itemPerPage;

        return response()->json([
            'totalPagination' => ceil($totalPaging),
            'data' => $data
        ], 200);
    }

    private function SearchMenuProfile($request)
    {
        $temp_column = null;

        $data = DB::table('menuGroups as mg')
            ->select(
                'mg.groupName',
            )
            ->where('mg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('mg.groupName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'mg.groupName';
        }

        return $temp_column;
    }

    private function SearchMenuSetting($request)
    {
        $temp_column = null;

        $data = DB::table('menuSettings as mg')
            ->select(
                'mg.groupName',
            )
            ->where('mg.isDeleted', '=', 0);

        if ($request->search) {
            $data = $data->where('mg.groupName', 'like', '%' . $request->search . '%');
        }

        $data = $data->get();

        if (count($data)) {
            $temp_column[] = 'mg.groupName';
        }

        return $temp_column;
    }

    public function insertMenuProfile(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string',
            'url' => 'required|string',
            'icon' => 'required|string',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuProfile::where('title', '=', $request->title)
            ->where('isDeleted', '=', 0)
            ->first();

        if ($menu) {
            return responseError('Menu Profile has already exists!');
        }

        DB::beginTransaction();
        try {
            menuProfile::create([
                'title' => $request->title,
                'url' => $request->url,
                'icon' => $request->icon,
                'userId' => $request->user()->id,
            ]);

            DB::commit();

            return responseCreate();
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Insert Failed',
                'errors' => $e,
            ]);
        }
    }

    public function insertMenuSetting(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'title' => 'required|string',
            'url' => 'required|string',
            'icon' => 'required|string',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuSettings::where('title', '=', $request->title)
            ->where('isDeleted', '=', 0)
            ->first();

        if ($menu) {
            return responseError('Menu Setting has already exists!');
        }

        DB::beginTransaction();
        try {
            menuSettings::create([
                'title' => $request->title,
                'url' => $request->url,
                'icon' => $request->icon,
                'userId' => $request->user()->id,
            ]);

            DB::commit();

            return responseCreate();
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Insert Failed',
                'errors' => $e,
            ]);
        }
    }

    public function insertMenuGroup(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'groupName' => 'required|string',
            'orderMenu' => 'required|integer',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuGroup::where('groupName', '=', $request->groupName)
            ->where('isDeleted', '=', 0)
            ->first();

        if ($menu) {
            return responseError('Menu Group has already exists!');
        }

        $order = menuGroup::select('orderMenu')
            ->where('isDeleted', '=', 0)
            ->orderby('orderMenu', 'desc')
            ->first();

        if (($order->orderMenu + 1) != $request->orderMenu) {
            return responseError('Order data is not valid!');
        }

        DB::beginTransaction();
        try {
            menuGroup::create([
                'groupName' => $request->groupName,
                'orderMenu' => $request->orderMenu,
                'userId' => $request->user()->id,
            ]);

            DB::commit();

            return responseCreate();
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Insert Failed',
                'errors' => $e,
            ]);
        }
    }

    public function insertChildrenMenu(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'groupId' => 'required|integer',
            'menuName' => 'required|string',
            'identify' => 'required|string',
            'title' => 'required|string',
            'type' => 'required|string',
            'icon' => 'required|string',
            'orderMenu' => 'required|integer',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuGroup::where('id', '=', $request->groupId)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('Menu Group is not exists!');
        }

        $order = childrenMenuGroups::select('orderMenu')
            ->where('isDeleted', '=', 0)
            ->orderby('orderMenu', 'desc')
            ->first();

        if (($order->orderMenu + 1) != $request->orderMenu) {
            return responseError('Order data is not valid!');
        }

        DB::beginTransaction();
        try {
            childrenMenuGroups::create([
                'groupId' => $request->groupId,
                'orderMenu' => $request->orderMenu,
                'menuName' => $request->menuName,
                'identify' => $request->identify,
                'title' => $request->title,
                'type' => $request->type,
                'icon' => $request->icon,
                'isActive' => $request->isActive,
                'userId' => $request->user()->id,
            ]);

            DB::commit();

            return responseCreate();
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Insert Failed',
                'errors' => $e,
            ]);
        }
    }

    public function insertGrandChildMenu(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'childrenId' => 'required|integer',
            'menuName' => 'required|string',
            'identify' => 'required|string',
            'title' => 'required|string',
            'type' => 'required|string',
            'url' => 'required|string',
            'orderMenu' => 'required|integer',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = childrenMenuGroups::where('id', '=', $request->childrenId)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('Children Menu Group is not exists!');
        }

        $order = grandChildrenMenuGroups::select('orderMenu')
            ->where('isDeleted', '=', 0)
            ->orderby('orderMenu', 'desc')
            ->first();

        if (($order->orderMenu + 1) != $request->orderMenu) {
            return responseError('Order data is not valid!');
        }

        DB::beginTransaction();
        try {
            grandChildrenMenuGroups::create([
                'childrenId' => $request->childrenId,
                'orderMenu' => $request->orderMenu,
                'menuName' => $request->menuName,
                'identify' => $request->identify,
                'title' => $request->title,
                'type' => $request->type,
                'url' => $request->url,
                'isActive' => $request->isActive,
                'userId' => $request->user()->id,
            ]);

            DB::commit();

            return responseCreate();
        } catch (\Throwable $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Insert Failed',
                'errors' => $e,
            ]);
        }
    }

    public function updateMenuGroup(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'groupName' => 'required|string',
            'orderMenu' => 'required|integer',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuGroup::where('id', '=', $request->id)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('There is no any Data found!');
        }

        $menu->groupName = $request->groupName;
        $menu->orderMenu = $request->orderMenu;
        $menu->userUpdateId = $request->user()->id;
        $menu->updated_at = \Carbon\Carbon::now();
        $menu->save();

        return responseUpdate();
    }

    public function updateMenuProfile(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'title' => 'required|string',
            'url' => 'required|string',
            'icon' => 'required|string',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuProfile::where('id', '=', $request->id)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('There is no any Data found!');
        }

        $menu->title = $request->title;
        $menu->url = $request->url;
        $menu->icon = $request->icon;
        $menu->userUpdateId = $request->user()->id;
        $menu->updated_at = \Carbon\Carbon::now();
        $menu->save();

        return responseUpdate();
    }

    public function updateMenuSetting(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'title' => 'required|string',
            'url' => 'required|string',
            'icon' => 'required|string',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = menuSettings::where('id', '=', $request->id)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('There is no any Data found!');
        }

        $menu->title = $request->title;
        $menu->url = $request->url;
        $menu->icon = $request->icon;
        $menu->userUpdateId = $request->user()->id;
        $menu->updated_at = \Carbon\Carbon::now();
        $menu->save();

        return responseUpdate();
    }

    public function updateChildMenu(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'groupId' => 'required|integer',
            'menuName' => 'required|string',
            'identify' => 'required|string',
            'title' => 'required|string',
            'type' => 'required|string',
            'icon' => 'required|string',
            'orderMenu' => 'required|integer',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = childrenMenuGroups::where('id', '=', $request->id)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('There is no any Data found!');
        }

        $menu->groupId = $request->groupId;
        $menu->menuName = $request->menuName;
        $menu->identify = $request->identify;
        $menu->title = $request->title;
        $menu->type = $request->type;
        $menu->icon = $request->icon;
        $menu->orderMenu = $request->orderMenu;
        $menu->userUpdateId = $request->user()->id;
        $menu->updated_at = \Carbon\Carbon::now();
        $menu->save();

        return responseUpdate();
    }

    public function updateGrandChildMenu(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'id' => 'required|integer',
            'childrenId' => 'required|integer',
            'menuName' => 'required|string',
            'identify' => 'required|string',
            'title' => 'required|string',
            'type' => 'required|string',
            'url' => 'required|string',
            'orderMenu' => 'required|integer',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors()->all();

            return response()->json([
                'message' => 'The given data was invalid.',
                'errors' => $errors,
            ], 422);
        }

        $menu = grandChildrenMenuGroups::where('id', '=', $request->id)
            ->where('isDeleted', '=', 0)
            ->first();

        if (!$menu) {
            return responseError('There is no any Data found!');
        }

        $menu->childrenId = $request->childrenId;
        $menu->menuName = $request->menuName;
        $menu->identify = $request->identify;
        $menu->title = $request->title;
        $menu->type = $request->type;
        $menu->url = $request->url;
        $menu->isActive = $request->isActive;
        $menu->orderMenu = $request->orderMenu;
        $menu->userUpdateId = $request->user()->id;
        $menu->updated_at = \Carbon\Carbon::now();
        $menu->save();

        return responseUpdate();
    }

    public function deleteMenuGroup(Request $request)
    {
        foreach ($request->id as $va) {
            $res = menuGroup::find($va);

            if (!$res) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['There is any Data not found!'],
                ], 422);
            }
        }

        foreach ($request->id as $va) {

            $menu = menuGroup::find($va);

            $menu->DeletedBy = $request->user()->id;
            $menu->isDeleted = true;
            $menu->DeletedAt = Carbon::now();
            $menu->save();
        }

        return responseDelete();
    }

    public function deleteMenuProfile(Request $request)
    {
        foreach ($request->id as $va) {
            $res = menuProfile::find($va);

            if (!$res) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['There is any Data not found!'],
                ], 422);
            }
        }

        foreach ($request->id as $va) {

            $menu = menuProfile::find($va);

            $menu->DeletedBy = $request->user()->id;
            $menu->isDeleted = true;
            $menu->DeletedAt = Carbon::now();
            $menu->save();
        }

        return responseDelete();
    }

    public function deleteMenuSetting(Request $request)
    {
        foreach ($request->id as $va) {
            $res = menuSettings::find($va);

            if (!$res) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['There is any Data not found!'],
                ], 422);
            }
        }

        foreach ($request->id as $va) {

            $menu = menuSettings::find($va);

            $menu->DeletedBy = $request->user()->id;
            $menu->isDeleted = true;
            $menu->DeletedAt = Carbon::now();
            $menu->save();
        }

        return responseDelete();
    }

    public function deleteChildMenu(Request $request)
    {
        foreach ($request->id as $va) {
            $res = childrenMenuGroups::find($va);

            if (!$res) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['There is any Data not found!'],
                ], 422);
            }
        }

        foreach ($request->id as $va) {

            $menu = childrenMenuGroups::find($va);

            $menu->DeletedBy = $request->user()->id;
            $menu->isDeleted = true;
            $menu->DeletedAt = Carbon::now();
            $menu->save();
        }

        return responseDelete();
    }

    public function deleteGrandChildMenu(Request $request)
    {
        foreach ($request->id as $va) {
            $res = grandChildrenMenuGroups::find($va);

            if (!$res) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => ['There is any Data not found!'],
                ], 422);
            }
        }

        foreach ($request->id as $va) {

            $menu = grandChildrenMenuGroups::find($va);

            $menu->DeletedBy = $request->user()->id;
            $menu->isDeleted = true;
            $menu->DeletedAt = Carbon::now();
            $menu->save();
        }

        return responseDelete();
    }
}
