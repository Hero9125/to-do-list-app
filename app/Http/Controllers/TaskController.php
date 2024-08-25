<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request; 


class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::all();
        return view('tasks.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:tasks,name',
        ]);

        Task::create(['name' => $request->name]);

        return response()->json(['success' => true]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
         $request->validate([
            'name' => 'required|unique:tasks,name,' . $task->id,
            'completed' => 'boolean',
        ]);

        $task->completed = $request->completed ? 1 : 0;
        $task->name      = $request->name;
        $task->save();

        return response()->json(['success' => true]);
    }

    public function checkStatus(Request $request, Task $task)
    {
        $task->update([
            'completed' => $request->completed,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json(['success' => true]);
    }

   /**
     * Display a listing  all.
     *
     * @return \Illuminate\Http\Response
     */
    public function showAll()
    {
        $tasks = Task::all();
        return response()->json($tasks);
    }
}
