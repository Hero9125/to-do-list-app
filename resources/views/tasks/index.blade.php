<!-- resources/views/tasks/index.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
       <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
                <div id="errorContainer"></div>


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
          $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $('#addTask').click(function() {
                var name = $('#taskName').val();

                // Check if task name is not empty
                if (name.trim() === '') {
                    alert('Task name cannot be empty');
                    return;
                }

                $.ajax({
                    url: '/tasks',
                    type: 'POST',
                    data: { name: name },
                    success: function(data) {
                        if (data.success) {
                            location.reload(); 
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) { 
                            var errors = xhr.responseJSON.errors;
                            displayErrors(errors);
                        } else {
                            alert('Error: ' + xhr.statusText);
                        }
                    }
                });
            });

            function displayErrors(errors) {
                var errorHtml = '<div class="alert alert-danger"><ul>';
                $.each(errors, function(field, messages) {
                    $.each(messages, function(index, message) {
                        errorHtml += '<li>' + message + '</li>';
                    });
                });
                errorHtml += '</ul></div>';

                $('#errorContainer').html(errorHtml);
            }

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
                          // $('#task-' + editTaskId).find('td:eq(1)').text(newName);

                        }
                    }   
                });
            });

            // Delete Task
            $(document).on('click', '.deleteTask', function() {
                if (confirm('Are you sure to delete this task?')) {
                    var id = $(this).data('id');
                    $.ajax({
                        url: '/tasks/' + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(data) {
                            if (data.success) {
                                Swal.fire({
                                    title: "Deleted successfully!", 
                                    icon: "success"
                                });
                                $('#task-' + id).remove();
                            }
                        }
                    });
                }
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
