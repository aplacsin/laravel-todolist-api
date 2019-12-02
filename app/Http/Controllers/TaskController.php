<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Task;
use DateTime;

class TaskController extends Controller
{

    use AuthenticatesUsers;

    private $success_status = 200;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $tasks = new Task;
        $tasks = $tasks->with('subtask')->where('parent_id', 0);
        $tasks = $tasks->get();

        $success['status'] = "success";
        $success['task'] = $tasks;        

        return response()->json(['success' => $success]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'title' => 'required',
                'description'   => 'required',
                'priority'   => 'required|integer|between:1,5',                
            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $taskInput = array(
            'parent_id' => 0,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,            
        );
 
        $tasks = Task::create($taskInput);
 
        if(!is_null($tasks)) {
            $success['status'] = "Task has been created";
            $success['data'] = $tasks;
        }
 
        return response()->json(['success' => $success], $this->success_status);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
/*     public function show($id)
    {
        //
    } */
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(),
        [             
            'title' => 'required',
            'description'   => 'required',
            'priority'   => 'required|integer|between:1,5',            
        ]
        );

        // if validation fails
        if($validator->fails()) {
            return response()->json(["validation errors" => $validator->errors()]);
        }
        
        $inputData = array(
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,   
        );

        $count = Task::where('status', 'done')->where('id', $id)->count();

        if($count == 0) {
            $tasks = Task::where('id', $id)->update($inputData);        
            if($tasks == 1) {
                $success['status'] = "success";
                $success['message'] = "Task has been updated successfully";
            } else {
                $success['status'] = "failed";
                $success['message'] = "Failed to update the task please try again";
            }
        } else {
            $success['status'] = "failed";
            $success['message'] = "Сannot update an already completed task";
        }
               
        return response()->json(['success' => $success], $this->success_status);  
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {        
        $count = Task::where('status', 'done')->where('id', $id)->count();
        
        if ($count == 0) {
            $tasks = Task::findOrFail($id);

            if(!is_null($tasks)) {  
                $response = Task::where('id', $id)->delete();
                if($response == 1) {
                    $success['status'] = 'success';
                    $success['message'] = 'Task has been deleted successfully'; 
                }
            }
        } else {
            $success['status'] = "failed";
            $success['message'] = "Unable to delete completed tasks";
        }
        
        return response()->json(['success' => $success], $this->success_status);
    }


    /* Created Subtask  */    
    public function createsubtask(Request $request, $parent_id)
    {        
        $validator = Validator::make($request->all(),
            [
                'title' => 'required',
                'description'   => 'required',
                'priority'   => 'required|integer|between:1,5',                
            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }        

        $subtaskInput = array(
            'parent_id' => $parent_id,
            'title' => $request->title,
            'description' => $request->description,
            'priority' => $request->priority,            
        );

        $count = Task::where('status', 'done')->where('id', $parent_id)->count();
        
        if ($count == 0) { 
            $subtasks = Task::create($subtaskInput);
            
            if(!is_null($subtasks)) {
                $success['status'] = "Subtask has been created";
                $success['data'] = $subtasks;
            }
        } else {
            $success['status'] = "failed";
            $success['message'] = "You cannot create a subtask for an already completed task";
        }
                
        return response()->json(['success' => $success], $this->success_status);
    }

    
    /* Completed Tasks */
    public function completed($id)
    {
        $count = Task::where('status', 'todo')->where('parent_id', $id)->count();
        
        if ($count == 0) {
            $currentDate = date("Y-m-d H:i:s");
            $tasks = Task::findOrFail($id);
            if(!is_null($tasks)) {
                $response = Task::where('id', $id)->update(['status' => 'done', 'completed_at' => $currentDate]);
                if($response == 1) {
                    $success['status'] = 'success';
                    $success['message'] = 'Task has been completed successfully';   
                }
            }
        } else {
            $success['status'] = "failed";
            $success['message'] = "Need to complete subtasks";
        }
        return response()->json(['success' => $success], $this->success_status);
    }
    

    /* Filter Tasks */
    public function filter(Request $request) 
    {        
        $validator = Validator::make($request->all(),
            [
                'title' => 'alpha_num',
                'priority'   => 'integer|between:1,5',
                'status' => 'in:todo,done',
                'sort' => 'in:priority,created_at,completed_at'
            ]
        );

        if($validator->fails()) {
            return response()->json(["validation_errors" => $validator->errors()]);
        }

        $title = $request->title;
        $priority = $request->priority;
        $status = $request->status;
        $sort = $request->sort;

        $tasks = new Task;
        $tasks = $tasks->with('subtask')->where('parent_id', 0);

        if ($request->has('title')) {
            $tasks->whereRaw("MATCH(title) AGAINST('{$title}' IN BOOLEAN MODE)");
        }

        if ($request->has('priority')) {
            $tasks->where('priority', $priority);
        }

        if ($request->has('status')) {
            $tasks->where('status', $status);
        }

        if ($request->has('sort')) {
            $tasks->orderBy("{$sort}", 'asc');
        }

        $tasks = $tasks->get();

        $success['status'] = "Task filtered";
        $success['data'] = $tasks;
        
        return response()->json(['success' => $success], $this->success_status);
    }
}
