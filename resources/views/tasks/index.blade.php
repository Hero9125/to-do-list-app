<!-- resources/views/tasks/index.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
       <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple To-Do List App</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="container mt-5">
        <h3 class="mb-3">PHP - Simple To Do List App</h3>
        <div class="input-group mb-3">
            <input type="text" id="taskName" class="form-control" placeholder="Enter task name" aria-label="Task Name">
            <button id="addTask" class="btn btn-primary">Add Task</button>
        </div>

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Task</th>
                    <th scope="col">Status</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody id="taskList">
                @foreach ($tasks as $task)
                    <tr id="task-{{ $task->id }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $task->name }}</td>
                        <td>
                            <input type="checkbox" class="taskStatus" data-id="{{ $task->id }}" {{ $task->completed ? 'checked' : '' }}>
                        </td>
                        <td>
                            <button class="btn btn-success editTask" data-id="{{ $task->id }}">Edit</button>
                            <button class="btn btn-danger deleteTask" data-id="{{ $task->id }}">&#10060;</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button id="showAllTasks" class="btn btn-info">Show All Tasks</button>
    </div>

    <!-- Edit Task Modal -->
    <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTaskModalLabel">Edit Task</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="text" id="editTaskName" class="form-control" placeholder="Enter new task name">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" id="saveTaskChanges" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Add Task
            $('#addTask').click(function() {
                var name = $('#taskName').val();
                $.post('/tasks', { name: name, _token: '{{ csrf_token() }}' }, function(data) {
                    if (data.success) {
                        location.reload();
                    }
                });
            });

            // Mark as Completed
            $(document).on('change', '.taskStatus', function() {
                var id = $(this).data('id');
                var completed = $(this).is(':checked'); 
                $.ajax({
                    url: '/check-status/' + id,
                    type: 'PATCH',
                    data: { completed: completed ? 1 : 0, _token: '{{ csrf_token() }}' },
                    success: function(data) {
                        if (data.success) {
                            $('#task-' + id).toggle(); 

                        }
                    }   
                });
            });

            // Delete Task
        $(document).on('click', '.deleteTask', function() {
            var id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: 'You won\'t be able to revert this!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/tasks/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(data) {
                            if (data.success) {
                                $('#task-' + id).remove();
                                Swal.fire(
                                    'Deleted!',
                                    'Your task has been deleted.',
                                    'success'
                                );
                            }
                        },
                        error: function() {
                            Swal.fire(
                                'Error!',
                                'There was an error deleting the task.',
                                'error'
                            );
                        }
                    });
                }
            });
        });


            // Show All Tasks
            $('#showAllTasks').click(function() {
                $.get('/tasks/all', function(data) {
                    $('#taskList').empty();
                    $.each(data, function(index, task) {
                        $('#taskList').append(`
                            <tr id="task-${task.id}">
                                <td>${index + 1}</td>
                                <td>${task.name}</td>
                                <td>
                                    <input type="checkbox" class="taskStatus" data-id="${task.id}" ${task.completed ? 'checked' : ''}>
                                </td>
                                <td>
                                    <button class="btn btn-success editTask" data-id="${task.id}">Edit</button>
                                    <button class="btn btn-danger deleteTask" data-id="${task.id}">&#10060;</button>
                                </td>
                            </tr>
                        `);
                    });
                });
            });

            // Edit Task
            var editTaskId;

            $(document).on('click', '.editTask', function() {
                editTaskId = $(this).data('id');
                var currentName = $('#task-' + editTaskId).find('td:eq(1)').text();
                $('#editTaskName').val(currentName);
                $('#editTaskModal').modal('show');
            });

            $('#saveTaskChanges').click(function() {
                var newName = $('#editTaskName').val();
                $.ajax({
                    url: '/tasks/' + editTaskId,
                    type: 'PATCH',
                    data: { name: newName, _token: '{{ csrf_token() }}' },
                    success: function(data) {
                        if (data.success) {
                            Swal.fire({
                              title: "Successfully updated!", 
                              icon: "success"
                            });
                            $('#editTaskModal').modal('hide');
                            $('#task-' + editTaskId).find('td:eq(1)').text(newName);
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
