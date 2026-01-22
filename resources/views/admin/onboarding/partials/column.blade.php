<div class="col-xxl-3 col-md-6">
    <div
        class="card bg-{{ $color }}-subtle border-0 shadow-none border-top border-3 border-{{ $color }} h-auto">
        <div class="card-header p-3 d-flex align-items-center justify-content-between border-0 pb-0">
            <h6 class="card-title mb-0">{{ $title }}</h6>
            <div class="d-flex gap-2">

            </div>
        </div>

        <div class="card-body p-3 d-grid gap-3" id="taskWrapper_{{ $id }}">
            @foreach ($tasks as $task)
                <div class="card card-action cursor-move action-border-{{ $color }} h-auto mb-0">
                    <div class="card-header p-3 d-flex align-items-center justify-content-between gap-2 border-0 pb-0">
                        <h6 class="card-title mb-0">{{ $task->title ?? 'Untitled Task' }}</h6>
                        <div class="d-flex">
                            <div class="btn-group bg-white">
                                <button class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-ellipsis"></i>
                                </button>

                                <ul class="dropdown-menu dropdown-menu-end">
                                    {{-- EDIT ACTION --}}
                                    <li>
                                        <a class="dropdown-item" href="{{ route('admin.onboarding.edit', $task->id) }}">
                                            <i class="fa-regular fa-pen-to-square me-2"></i> Edit
                                        </a>
                                    </li>

                                    {{-- DELETE ACTION (Using Form) --}}
                                    <li>
                                        <form action="{{ route('admin.onboarding.destroy', $task->id) }}" method="POST"
                                            onsubmit="return confirm('Are you sure you want to delete this task?');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="fa-regular fa-trash-can me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-2 p-3 text-1xs">
                        <p class="mb-2">{{ Str::limit($task->content, 60) }}</p>

                        <div class="d-flex gap-2 mb-3">

                            @if ($task->start_date)
                                <div class="text-start w-50">
                                    <span>Start Date</span>
                                    <span
                                        class="text-dark d-block fw-semibold">{{ $task->start_date->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if ($task->due_date)
                                <div class="text-start w-50">
                                    <span>End Date</span>
                                    <span
                                        class="text-dark d-block fw-semibold">{{ $task->due_date->format('d M Y') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2 justify-content-between align-items-center">
                            <div class="avatar-group">
                                {{-- Employee Avatar --}}
                                @if ($task->employee)
                                    <div class="avatar avatar-xs rounded-circle border border-2 border-white"
                                        title="{{ $task->employee->full_name }}">
                                        <img src="{{ $task->employee->profile_image_url ?? asset('assets/images/default-user.png') }}"
                                            alt="">
                                    </div>
                                @endif
                            </div>

                            {{-- Status Dropdown --}}
                            <div class="dropdown select-status">
                                <button
                                    class="btn btn-sm btn-subtle-{{ $color }} dropdown-toggle waves-effect waves-light"
                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $title }}
                                    <i class="fa-solid fa-angle-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item status-changer" href="#"
                                            data-id="{{ $task->id }}" data-status="new">New</a></li>
                                    <li><a class="dropdown-item status-changer" href="#"
                                            data-id="{{ $task->id }}" data-status="in_progress">In Progress</a>
                                    </li>
                                    <li><a class="dropdown-item status-changer" href="#"
                                            data-id="{{ $task->id }}" data-status="pending">Pending</a></li>
                                    <li><a class="dropdown-item status-changer" href="#"
                                            data-id="{{ $task->id }}" data-status="completed">Done</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
