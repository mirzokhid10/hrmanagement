@push('scripts')
<script>
// Make variables available globally
const isAdmin = {{ Auth::user()->isAdmin() ? 'true' : 'false' }};

// 1. Load Departments (For Admin)
window.loadCompanyData = function(companyId) {
    if(!companyId) return;
    fetch(`/admin/companies/${companyId}/departments`)
        .then(res => res.json())
        .then(data => {
            let html = '<option value="" selected disabled>Choose Department...</option>';
            data.forEach(d => html += `<option value="${d.id}">${d.name}</option>`);

            // Update dropdowns in all potential places (Modal, Offcanvas, or Main page)
            const deptSelect = document.getElementById('department_id');
            if(deptSelect) deptSelect.innerHTML = html;
        });
};

// 2. Toggle Visibility based on Audience Type
window.toggleAudience = function() {
    // Find which radio is checked
    const checkedRadio = document.querySelector('input[name="audience_type"]:checked');
    if(!checkedRadio) return; // Safety check

    const type = checkedRadio.value;
    const deptWrapper = document.getElementById('dept_wrapper');
    const empWrapper = document.getElementById('emp_wrapper');

    if(!deptWrapper || !empWrapper) return;

    // Reset
    deptWrapper.style.display = 'none';
    empWrapper.style.display = 'none';

    if (type === 'department') {
        deptWrapper.style.display = 'block';
    } else if (type === 'employees') {
        deptWrapper.style.display = 'block';
        empWrapper.style.display = 'block';
        window.loadEmployeesInDept(); // Trigger load immediately if dept is already selected
    }
};

// 3. Load Employees
window.loadEmployeesInDept = function() {
    const deptId = document.getElementById('department_id').value;

    // Ensure we are in "Specific People" mode
    const empRadio = document.querySelector('input[name="audience_type"][value="employees"]');
    if (!empRadio || !empRadio.checked || !deptId) return;

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
        });
};

// 4. Initial Load (For HR)
document.addEventListener('DOMContentLoaded', function() {
    if (!isAdmin) {
        // If HR, the company ID is hidden but present. Load departments automatically.
        const hiddenCompanyId = document.getElementById('company_id');
        if(hiddenCompanyId) window.loadCompanyData(hiddenCompanyId.value);
    }
});
</script>
@endpush
