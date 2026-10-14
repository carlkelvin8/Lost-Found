document.addEventListener('DOMContentLoaded', () => {
    const section = document.getElementById('studentFinderSection');
    if (!section) return;
    const form = section.closest('form');
    const type = form.elements.report_type;
    const checkbox = document.getElementById('studentSurrenderCheckbox');
    const defaultInput = document.getElementById('studentSurrenderDefault');
    const name = form.elements.finder_name;
    const studentId = form.elements.finder_student_id;
    function validateFinder() {
        const found = type.value === 'found';
        const student = found && checkbox.checked;
        section.classList.toggle('d-none', !found);
        checkbox.disabled = defaultInput.disabled = !found;
        document.getElementById('studentFinderFields').classList.toggle('d-none', !student);
        [name, studentId].forEach(input => {
            input.disabled = !student;
            input.required = student;
            input.setCustomValidity(student && !input.value.trim() ? 'Finder name and student ID are required for a student surrender.' : '');
        });
    }
    [type, checkbox, name, studentId].forEach(input => input.addEventListener('input', validateFinder));
    form.addEventListener('submit', validateFinder);
    validateFinder();
});
