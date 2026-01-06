 <div class="modal fade" id="editEmployeeModal" tabindex="-1" aria-hidden="true">
     <div class="modal-dialog modal-dialog-centered modal-lg">
         <div class="modal-content">
             {{-- This alert-danger block is useful for debugging if validation errors redirect back to the page --}}
             @if ($errors->any() && session('edit_modal_open'))
                 <div class="alert alert-danger">
                     <ul>
                         @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                         @endforeach
                     </ul>
                 </div>
             @endif
             <div class="modal-header py-3">
                 <h5 class="modal-title">Edit Employee</h5> {{-- Correct Title --}}
                 <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
             </div>
             <div class="modal-body">
                 <form id="editEmployeeForm" action="" method="POST" enctype="multipart/form-data">
                     {{-- Empty action, set by JS --}}
                     @csrf
                     @method('PUT') {{-- IMPORTANT: For update requests --}}

                     <input type="hidden" name="employee_id" id="edit_employee_id"> {{-- Hidden field for employee ID --}}

                     <div class="row">
                         <div class="mb-3 col-md-6">
                             <label for="edit_firstName" class="form-label">First Name <span
                                     class="text-danger">*</span></label>
                             <input type="text" name="first_name"
                                 class="form-control @error('first_name') is-invalid @enderror" id="edit_firstName"
                                 placeholder="Enter first name"> {{-- No old() here, JS will populate --}}
                             @error('first_name')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                         <div class="mb-3 col-md-6">
                             <label for="edit_lastName" class="form-label">Last Name <span
                                     class="text-danger">*</span></label>
                             <input type="text" name="last_name"
                                 class="form-control @error('last_name') is-invalid @enderror" id="edit_lastName"
                                 placeholder="Enter last name">
                             @error('last_name')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                     </div>
                     <div class="row">
                         <div class="col-md-6 mb-3">
                             <label for="edit_email" class="form-label">Email Address <span
                                     class="text-danger">*</span></label>
                             <input type="email" name="email"
                                 class="form-control @error('email') is-invalid @enderror" id="edit_email"
                                 placeholder="example@email.com">
                             @error('email')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                         <div class="col-md-6 mb-3">
                             <label for="edit_phone" class="form-label">Phone Number</label>
                             <input type="tel" name="phone_number"
                                 class="form-control @error('phone_number') is-invalid @enderror" id="edit_phone"
                                 placeholder="+91 9876543210">
                             @error('phone_number')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                     </div>
                     <div class="row">
                         <div class="col-md-6 mb-3">
                             <label for="edit_department" class="form-label">Department <span
                                     class="text-danger">*</span></label>
                             <select class="form-select @error('department_id') is-invalid @enderror"
                                 name="department_id" id="edit_department">
                                 <option value="" selected disabled>Select Department</option>
                                 {{-- $departments variable should be passed to the view where this modal is included --}}
                                 @foreach ($departments as $department)
                                     <option value="{{ $department->id }}">{{ $department->name }}</option>
                                 @endforeach
                             </select>
                             @error('department_id')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                         <div class="col-md-6 mb-3">
                             <label for="edit_designation" class="form-label">Designation <span
                                     class="text-danger">*</span></label>
                             <input type="text" name="job_title"
                                 class="form-control @error('job_title') is-invalid @enderror" id="edit_designation"
                                 placeholder="e.g. Software Engineer">
                             @error('job_title')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                     </div>
                     <div class="row">
                         <div class="col-md-6 mb-3">
                             <label for="edit_joiningDate" class="form-label">Joining Date <span
                                     class="text-danger">*</span></label>
                             <input type="date" name="hire_date"
                                 class="form-control @error('hire_date') is-invalid @enderror" id="edit_joiningDate">
                             @error('hire_date')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                         <div class="col-md-6 mb-3">
                             <label for="edit_status" class="form-label">Employment Status <span
                                     class="text-danger">*</span></label>
                             <select class="form-select @error('status') is-invalid @enderror" name="status"
                                 id="edit_status">
                                 <option value="Active">Active</option>
                                 <option value="Inactive">Inactive</option>
                                 <option value="Probation">Probation</option>
                                 <option value="Terminated">Terminated</option>
                             </select>
                             @error('status')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                     </div>
                     <div class="row">
                         <div class="col-md-6 mb-3">
                             <label for="edit_dateOfBirth" class="form-label">Date Of Birth</label>
                             <input type="date" name="date_of_birth"
                                 class="form-control @error('date_of_birth') is-invalid @enderror"
                                 id="edit_dateOfBirth">
                             @error('date_of_birth')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                         <div class="mb-3 col-md-6">
                             <label for="edit_salary" class="form-label">Salary</label>
                             <input class="form-control @error('salary') is-invalid @enderror" name="salary"
                                 type="text" id="edit_salary">
                             @error('salary')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                         </div>
                     </div>
                     <div class="mb-3">
                         <label for="edit_address" class="form-label">Address</label>
                         <textarea class="form-control @error('address') is-invalid @enderror" name="address" id="edit_address"
                             rows="2" placeholder="Enter address"></textarea>
                         @error('address')
                             <div class="invalid-feedback">{{ $message }}</div>
                         @enderror
                     </div>
                     <div class="row">
                         <div class="mb-3 col-md-6">
                             <label for="edit_photo" class="form-label">Profile Photo</label>
                             <input class="form-control @error('image') is-invalid @enderror" name="image"
                                 type="file" id="edit_photo">
                             @error('image')
                                 <div class="invalid-feedback">{{ $message }}</div>
                             @enderror
                             <div id="current_profile_image_container" class="mt-2" style="display: none;">
                                 Current: <img src="" id="current_profile_image_preview" alt="Profile"
                                     style="width: 50px; height: 50px; object-fit: cover;">
                                 <div class="form-check">
                                     <input class="form-check-input" type="checkbox" name="remove_image"
                                         value="1" id="removeImageCheckbox">
                                     <label class="form-check-label" for="removeImageCheckbox">Remove current
                                         photo</label>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="text-end">
                         <button type="submit" class="btn btn-primary">Update Employee</button> {{-- Correct Button Text --}}
                     </div>
                 </form>
             </div>
         </div>
     </div>
 </div>
