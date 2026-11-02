/* Uses the existing lesson creation and upload forms; does not publish or assign. */
window.openInterventionDraft = function (activityId, mode) {
    const root = document.getElementById('intervention-' + activityId);
    const draft = JSON.parse(root.dataset.interventionDraft);
    const ideas = document.getElementById('intervention-ideas-text-' + activityId);
    const approved = document.getElementById('intervention-ideas-approved-' + activityId);
    if (ideas?.value && !approved.checked) {
        document.getElementById('intervention-status-' + activityId).textContent = 'Review and approve the ideas before adding them to a draft.'; return;
    }
    window.signedInterventionContext = {activityId, mode};
    document.getElementById('newStepLpTitle').value = draft.title;
    document.getElementById('newStepLpDomain').value = draft.pdsp_domain;
    document.getElementById('newInterventionSkill').value = draft.learning_competency;
    document.getElementById('newInterventionTags').value = draft.tags;
    document.getElementById('newInterventionDescription').value = ideas?.value || draft.description;
    document.getElementById('newInterventionReviewed').checked = false;
    document.getElementById('interventionTargetSkillNotice').hidden = !draft.requires_target_skill;
    document.getElementById('interventionDraftFields').hidden = false;
    document.getElementById('newLessonPlanStepForm').style.display = 'block';
    document.getElementById('btnCreateStepLp').textContent = mode === 'upload' ? 'Save Private Draft & Upload Material' : 'Save Private Draft & Open Slide Builder';
    new bootstrap.Modal(document.getElementById('interactiveLessonChoiceModal')).show();
};
window.suggestInterventionIdeas = async function (activityId, studentId, button) {
    const status = document.getElementById('intervention-status-' + activityId);
    button.disabled = true; status.textContent = 'Preparing optional teaching ideas…';
    try {
        const response = await fetch(window.signedInterventionBase + '/iep/implementation/intervention-ideas', {
            method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({activity_id:activityId,student_id:studentId})
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'Unable to suggest ideas.');
        document.getElementById('intervention-ideas-' + activityId).hidden = false;
        document.getElementById('intervention-ideas-text-' + activityId).value = data.ideas.join('\n\n');
        document.getElementById('intervention-ideas-approved-' + activityId).checked = false;
        status.textContent = data.source === 'gemini' ? 'AI suggestions: edit and review before use.' : 'Local rule-based suggestions: edit and review before use.';
    } catch (error) { status.textContent = error.message; }
    finally { button.disabled = false; }
};
document.addEventListener('DOMContentLoaded', () => {
    ['newStepLpTitle','newStepLpDomain','newInterventionSkill','newInterventionTags','newInterventionDescription'].forEach(id => {
        ['input','change'].forEach(event => document.getElementById(id)?.addEventListener(event, () => { document.getElementById('newInterventionReviewed').checked = false; }));
    });
    const id = new URLSearchParams(window.location.search).get('intervention_upload');
    const draft = window.signedResourceDrafts?.[id];
    const select = document.getElementById('matFileLessonPlan');
    if (id && draft && [...select.options].some(option => option.value === id)) {
        select.value = id;
        document.getElementById('matFileTitle').value = draft.title;
        document.getElementById('matInterventionFields').hidden = false;
        document.getElementById('matInterventionSkill').value = draft.learning_competency;
        document.getElementById('matInterventionTags').value = draft.tags;
        document.getElementById('matInterventionDescription').value = draft.description;
        document.getElementById('matInterventionReviewed').checked = false;
        ['matFileTitle','matInterventionSkill','matInterventionTags','matInterventionDescription','matFileLessonPlan'].forEach(field => {
            ['input','change'].forEach(event => document.getElementById(field).addEventListener(event, () => { document.getElementById('matInterventionReviewed').checked = false; }));
        });
        select.addEventListener('change', () => {
            const selected = window.signedResourceDrafts?.[select.value];
            document.getElementById('matInterventionFields').hidden = !selected;
            if (selected) {
                document.getElementById('matInterventionSkill').value = selected.learning_competency;
                document.getElementById('matInterventionTags').value = selected.tags;
                document.getElementById('matInterventionDescription').value = selected.description;
            }
            document.getElementById('matInterventionReviewed').checked = false;
        });
        new bootstrap.Modal(document.getElementById('matFileModal')).show();
    }
});
