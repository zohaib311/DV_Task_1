<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('[data-assessment-form]');
        if (!form) return;

        const totalInput = document.getElementById('total_marks');
        const componentInputs = ['attendance_marks', 'mid_marks', 'final_marks']
            .map((id) => document.getElementById(id));
        const indicator = document.getElementById('assessment_scheme_total');

        function updateAssessmentTotal() {
            const componentTotal = componentInputs.reduce((total, input) => total + (Number(input.value) || 0), 0);
            const totalMarks = Number(totalInput.value) || 0;
            indicator.textContent = `${componentTotal} / ${totalMarks}`;
            indicator.classList.toggle('is-valid', componentTotal === totalMarks && totalMarks > 0);
            indicator.classList.toggle('is-invalid', componentTotal !== totalMarks);
        }

        [totalInput, ...componentInputs].forEach((input) => input.addEventListener('input', updateAssessmentTotal));
        updateAssessmentTotal();
    });
</script>
