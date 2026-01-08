<?php

namespace App\DataTables;

use App\Models\TimeOff;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TimeOffDataTable extends DataTable
{
    /**
     * Build the DataTable class.
     *
     * @param QueryBuilder<TimeOff> $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                // Render Avatar and Name
                $img = $row->employee->profile_image_url ?? asset('assets/default-avatar.png');
                return '
                    <div class="d-flex align-items-center mw-175px">
                        <div class="avatar avatar-xxs rounded-circle me-2">
                            <img src="' . $img . '" alt="avatar" class="rounded-circle" width="30">
                        </div>
                        <div class="d-flex flex-column">
                            <span class="text-gray-800 text-hover-primary mb-1">' . e($row->employee->full_name) . '</span>
                        </div>
                    </div>';
            })
            ->addColumn('leave_type', function ($row) {
                // Color Logic based on type name
                $class = match ($row->type->name) {
                    'Vacation' => 'text-success bg-light-success',
                    'Sick Leave' => 'text-danger bg-light-danger',
                    'Personal Leave' => 'text-warning bg-light-warning',
                    default => 'text-primary bg-light-primary'
                };
                return '<span class="badge fs-14 ' . $class . ' px-2 py-1">' . e($row->type->name) . '</span>';
            })
            ->addColumn('job_title', function ($row) {
                return $row->employee->job_title ?? '-';
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date ? $row->start_date->format('j M Y') : '-';
            })
            ->editColumn('end_date', function ($row) {
                return $row->end_date ? $row->end_date->format('j M Y') : '-';
            })
            ->editColumn('status', function ($row) {
                // Status Badge
                $class = match ($row->status) {
                    'Approved' => 'btn btn-subtle-success',
                    'Pending' => 'btn btn-subtle-warning',
                    'Rejected' => 'btn btn-subtle-danger',
                    default => 'btn btn-subtle-secondary'
                };
                return '<span class="badge ' . $class . '">' . $row->status . '</span>';
            })
            ->addColumn('action', function ($row) {
                // Edit and Delete Buttons (Pure Laravel Links/Forms)

                return '
                    <div class="d-flex justify-content-end flex-shrink-0">
                        <a href="' . route('admin.time-offs.edit', $row->id) . '" class="btn btn-icon btn-subtle-telegram waves-effect waves-light me-1">
                            <i class="fa-regular fa-pen-to-square"></i>
                        </a>
                        <form action="' . route('admin.time-offs.destroy', $row->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure?\')">
                            ' . csrf_field() . ' ' . method_field('DELETE') . '
                            <button type="submit" class="btn btn-icon btn-subtle-youtube waves-effect waves-light">
                                <i class="fa-regular fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                ';
            })
            ->rawColumns(['employee_name', 'leave_type', 'status', 'action']);
    }


    /**
     * Get the query source of dataTable.
     *
     * @return QueryBuilder<TimeOff>
     */
    public function query(TimeOff $model): QueryBuilder
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $query = $model->newQuery();

        if ($user->hasRole('admin')) {
            $query->withoutGlobalScope(\App\Scopes\TenantScope::class);
            $query->with([
                'employee' => function ($q) {
                    $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
                },
                'type' => function ($q) {
                    $q->withoutGlobalScope(\App\Scopes\TenantScope::class);
                }
            ]);
        } else {
            if ($user->company_id) {
                $query->where('company_id', $user->company_id);
            }
            $query->with(['employee', 'type']);
        }

        return $query;
    }

    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('time-off-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>') // Clean DOM
            ->orderBy(4, 'desc') // Order by Start Date
            ->selectStyleSingle()
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
            ]);
    }

    public function getColumns(): array
    {
        return [
            Column::make('employee_name')->title('Name'),
            Column::make('leave_type')->title('Leave Type'),
            Column::make('job_title')->title('Job Title'),
            Column::make('total_days')->title('Days')->addClass('text-start'),
            Column::make('start_date')->title('Start'),
            Column::make('end_date')->title('End'),
            Column::make('status')->title('Status'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->width(100)
                ->addClass('text-end'),
        ];
    }
}
