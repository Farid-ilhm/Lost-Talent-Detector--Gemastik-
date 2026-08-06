function toggleFormFields() {
    var roleSelect = document.getElementById('role');
    var role = roleSelect.value;

    // Dynamic Name Label
    var labelName = document.getElementById('label-name');
    if (role === 'institusi') {
        labelName.textContent = 'Nama Institusi:';
    } else {
        labelName.textContent = 'Nama Lengkap:';
    }

    // Container Elements
    var npsnContainer = document.getElementById('npsn-container');
    var addressContainer = document.getElementById('address-container');
    var schoolContainer = document.getElementById('school-container');
    var nisnContainer = document.getElementById('nisn-container');
    var classContainer = document.getElementById('class-container');
    var majorContainer = document.getElementById('major-container');
    var nimContainer = document.getElementById('nim-container');
    var semesterContainer = document.getElementById('semester-container');

    // Input Elements
    var npsnInput = document.getElementById('npsn');
    var addressInput = document.getElementById('address');
    var nisnInput = document.getElementById('nisn');
    var classroomInput = document.getElementById('classroom');
    var majorInput = document.getElementById('major');
    var nimInput = document.getElementById('nim');
    var semSelect = document.getElementById('semester');

    // Default: Hide all conditional blocks
    npsnContainer.style.display = 'none';
    if (addressContainer) addressContainer.style.display = 'none';
    schoolContainer.style.display = 'none';
    nisnContainer.style.display = 'none';
    classContainer.style.display = 'none';
    majorContainer.style.display = 'none';
    nimContainer.style.display = 'none';
    semesterContainer.style.display = 'none';

    // Clear required tags
    npsnInput.removeAttribute('required');
    if (addressInput) addressInput.removeAttribute('required');
    nisnInput.removeAttribute('required');
    classroomInput.removeAttribute('required');
    majorInput.removeAttribute('required');
    nimInput.removeAttribute('required');
    semSelect.removeAttribute('required');

    if (role === 'institusi') {
        npsnContainer.style.display = 'block';
        npsnInput.setAttribute('required', 'required');
        if (addressContainer && addressInput) {
            addressContainer.style.display = 'block';
            addressInput.setAttribute('required', 'required');
        }
    } else if (role === 'siswa') {
        // Move major input to be right after class input
        classContainer.parentNode.insertBefore(majorContainer, classContainer.nextSibling);

        schoolContainer.style.display = 'block';
        nisnContainer.style.display = 'block';
        classContainer.style.display = 'block';
        majorContainer.style.display = 'block';
        
        nisnInput.setAttribute('required', 'required');
        classroomInput.setAttribute('required', 'required');
        majorInput.setAttribute('required', 'required');
    } else if (role === 'mahasiswa') {
        // Move major input to be right after semester input
        semesterContainer.parentNode.insertBefore(majorContainer, semesterContainer.nextSibling);

        schoolContainer.style.display = 'block';
        nimContainer.style.display = 'block';
        semesterContainer.style.display = 'block';
        majorContainer.style.display = 'block';
        
        nimInput.setAttribute('required', 'required');
        semSelect.setAttribute('required', 'required');
        majorInput.setAttribute('required', 'required');
    }
}

document.getElementById('role').addEventListener('change', toggleFormFields);

window.addEventListener('load', function() {
    toggleFormFields();
});
