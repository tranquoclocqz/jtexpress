<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FullCalendar;
use Illuminate\Support\Facades\Validator;

class fullCalendarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $FullCalendar = new FullCalendar();
        $year = $request->year;
        $month = $request->month;
        $items = $FullCalendar->select("id", "title", "date")->whereRaw("YEAR(date) = ? AND MONTH(date) = ?", [$year, $month])->get();
        return response()->json($items, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'title' => 'required|min:10|max:255',
                'date' => 'required|date|date_format:Y-m-d'
            ],
            [
                'title.required' => 'Vui lòng nhập tiêu đề sự kiện',
                'title.min' => 'Tiêu đề sự kiện tối thiểu :min ký tự',
                'title.max' => 'Tiêu đề sự kiện tối đa :max ký tự',
                'date.required' => 'Vui lòng chọn thời gian',
                'date.date' => 'Sai định dạng ngày tháng',
                'date.date_format' => 'Sai định dạng ngày tháng :date_format',
            ]
        );
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['success' => 'fail', 'errors' => $errors], 200);
        }
        $FullCalendar = new FullCalendar();
        $FullCalendar->title = $request->title;
        $FullCalendar->date = $request->date;
        if ($FullCalendar->save()) {
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['success' => 'fail', 'messages' => 'Sự kiện lưu không được vui lòng kiểm tra lại'], 200);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $rules = ['date' => 'required|date|date_format:Y-m-d'];
        $messages = [
            'date.required' => 'Vui lòng chọn thời gian',
            'date.date' => 'Sai định dạng ngày tháng',
            'date.date_format' => 'Sai định dạng ngày tháng :date_format'
        ];
        if (isset($request->title)) {
            $rules['title'] = 'required|min:10|max:255';
            $messages['title.required'] = 'Vui lòng nhập tiêu đề sự kiện';
            $messages['title.min'] = 'Tiêu đề sự kiện tối thiểu :min ký tự';
            $messages['title.max'] = 'Tiêu đề sự kiện tối đa :max ký tự';
        }
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            return response()->json(['success' => 'fail', 'errors' => $errors], 200);
        }
        $FullCalendar = new FullCalendar();
        $FullCalendar = $FullCalendar->find($id);
        if (isset($request->title)) {
            $FullCalendar->title = $request->title;
        }
        $FullCalendar->date = $request->date;
        if ($FullCalendar->save()) {
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['success' => 'fail', 'messages' => 'Sự kiện cập nhật không được vui lòng kiểm tra lại'], 200);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $FullCalendar = new FullCalendar();
        $FullCalendar = $FullCalendar->find($id);
        if(!$FullCalendar){
            return response()->json(['success' => 'fail', 'errors' => 'Sự kiện không tồn tại'], 200);
        }
        if ($FullCalendar->delete()) {
            return response()->json(['success' => 'success'], 200);
        } else {
            return response()->json(['success' => 'fail', 'errors' => 'Sự kiện xóa không được vui lòng kiểm tra lại'], 200);
        }
    }
}
