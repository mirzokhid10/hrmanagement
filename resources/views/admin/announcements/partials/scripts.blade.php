@push('scripts')
    <script>
        // Make variables available globally
        const isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};

        // 1. Load Departments (For Admin)
        window.loadCompanyData = function(companyId) {
            if(!companyId) return;

            // Reset dropdowns
            document.getElementById('department_id').innerHTML = '<option disabled>Loading...</option>';

            fetch(`/admin/companies/${companyId}/departments`)
                .then(res => res.json())
                .then(data => {
                    let html = '<option value="" selected disabled>Choose Department...</option>';
                    data.forEach(d => html += `<option value="${d.id}">${d.name}</option>`);
                    document.getElementById('department_id').innerHTML = html;
                });
        };

        // 2. Toggle Visibility based on Dropdown Selection
        window.toggleAudience = function() {
            // Get value from SELECT dropdown
            const type = document.getElementById('audience_type').value;

            const deptWrapper = document.getElementById('dept_wrapper');
            const empWrapper = document.getElementById('emp_wrapper');
            const deptSelect = document.getElementById('department_id');
            const empSelect = document.getElementById('employee_ids');

            // Reset Display
            deptWrapper.style.display = 'none';
            empWrapper.style.display = 'none';

            // Reset Required Attributes (to prevent validation errors on hidden fields)
            deptSelect.required = false;
            empSelect.required = false;

            if (type === 'department') {
                deptWrapper.style.display = 'block';
                deptSelect.required = true;
            }
            else if (type === 'employees') {
                deptWrapper.style.display = 'block';
                empWrapper.style.display = 'block';

                deptSelect.required = true;
                empSelect.required = true;

                // Trigger load immediately if dept is already selected
                window.loadEmployeesInDept();
            }
        };

        // 3. Load Employees
        window.loadEmployeesInDept = function() {
            const deptId = document.getElementById('department_id').value;
            const type = document.getElementById('audience_type').value;

            // Only run if we are in "Specific People" mode
            if (type !== 'employees' || !deptId) return;

            const empSelect = document.getElementById('employee_ids');
            empSelect.innerHTML = '<option disabled>Loading...</option>';

            fetch(`/admin/departments/${deptId}/employees-ajax`)
                .then(res => res.json())
                .then(data => {
                    let html = '';
                    if(data.length === 0) {
                        html = '<option disabled>No employees found</option>';
                    } else {
                        data.forEach(e => html += `<option value="${e.id}">${e.name}</option>`);
                    }
                    empSelect.innerHTML = html;
                })
                .catch(err => {
                    console.error(err);
                    empSelect.innerHTML = '<option disabled>Error loading</option>';
                });
        };

        // 4. Initial Load (For HR)
        document.addEventListener('DOMContentLoaded', function() {
            if (!isAdmin) {
                // HR already has company_id in hidden field
                // No need to load departments via AJAX as they are injected via Blade
                // But we might need to reset the form state
                toggleAudience();
            }
        });
    </script>
@endpush
