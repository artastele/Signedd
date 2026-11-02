'use strict';
const fs=require('node:fs'), vm=require('node:vm'), assert=require('node:assert/strict');
class Field {
    constructor(id){this.id=id;this.value='';this.checked=false;this.hidden=false;this.style={};this.dataset={};this.events={};}
    addEventListener(type,handler){this.events[type]=handler;}
    focus(){}
}
async function main(){
    const ids=['intervention-19','intervention-ideas-text-19','intervention-ideas-approved-19','intervention-status-19','intervention-ideas-19',
        'newStepLpTitle','newStepLpDomain','newInterventionSkill','newInterventionTags','newInterventionDescription','newInterventionReviewed',
        'interventionDraftFields','interventionTargetSkillNotice','newLessonPlanStepForm','btnCreateStepLp','interactiveLessonChoiceModal','matFileLessonPlan','matFileTitle',
        'matInterventionFields','matInterventionSkill','matInterventionTags','matInterventionDescription','matInterventionReviewed','matFileModal'];
    const fields=Object.fromEntries(ids.map(id=>[id,new Field(id)]));
    const draft={title:'Practice: Addition',learning_competency:'addition',tags:'addition, within 10',description:'Use counters.',pdsp_domain:'perceptuo_cognitive'};
    fields['intervention-19'].dataset.interventionDraft=JSON.stringify(draft);
    fields.matFileLessonPlan.options=[{value:'901'}];
    let loaded,requests=[],opened=[],alerts=[];
    const window={signedInterventionBase:'/Signedd/public',signedResourceDrafts:{901:draft},location:{search:'?intervention_upload=901',href:''}};
    const context={window,BASE:'/Signedd/public',URLSearchParams,document:{getElementById:id=>fields[id],addEventListener:(event,handler)=>{loaded=handler;}},
        bootstrap:{Modal:class{constructor(element){this.element=element;}show(){opened.push(this.element.id);}}},
        alert:message=>alerts.push(message),
        fetch:async(url,options)=>{requests.push({url,body:JSON.parse(options.body)});return {ok:true,json:async()=>url.endsWith('/intervention-ideas')?{success:true,source:'local_rules',ideas:['Model addition with counters.']}:{success:true,lesson_plan_id:901}};}};
    vm.createContext(context);vm.runInContext(fs.readFileSync('public/js/learning-interventions.js','utf8'),context);loaded();
    let checks=0;const check=(condition,message)=>{checks++;assert.ok(condition,message);};
    check(opened[0]==='matFileModal'&&fields.matFileLessonPlan.value==='901','Upload handoff opens existing file material dialog on the private draft');
    check(fields.matInterventionSkill.value==='addition'&&fields.matInterventionTags.value==='addition, within 10','Upload retains editable competency/tag defaults');
    window.openInterventionDraft(19,'builder');
    check(opened.at(-1)==='interactiveLessonChoiceModal'&&fields.newStepLpTitle.value===draft.title,'Builder action reuses existing creation form with safe title defaults');
    check(fields.newInterventionSkill.value==='addition'&&!fields.newInterventionReviewed.checked,'Draft defaults require fresh teacher approval');
    check(requests.length===0,'Opening a draft never assigns, publishes or even saves');
    fields.newInterventionReviewed.checked=true;fields.newInterventionTags.events.input();
    check(!fields.newInterventionReviewed.checked,'Editing draft details invalidates approval');
    const button=new Field('ideas');await window.suggestInterventionIdeas(19,5,button);
    check(fields['intervention-ideas-text-19'].value==='Model addition with counters.'&&!fields['intervention-ideas-approved-19'].checked,'Fallback ideas remain editable and unapproved');
    const before=opened.length;window.openInterventionDraft(19,'builder');
    check(opened.length===before,'Unreviewed ideas cannot be copied into creation defaults');
    fields['intervention-ideas-approved-19'].checked=true;window.openInterventionDraft(19,'builder');
    check(fields.newInterventionDescription.value==='Model addition with counters.','Reviewed edited ideas transfer to existing builder form');
    const source=fs.readFileSync('app/Views/iep_implementation/workspace.php','utf8');
    const start=source.indexOf('function createStepLessonPlanAndOpenStudio()');
    const end=source.indexOf('/* ----------------------------------------------------------------',start);
    vm.runInContext(source.slice(start,end).replace(/<\?php[\s\S]*?\?>/g,'8'),context);
    fields.newInterventionReviewed.checked=false;context.createStepLessonPlanAndOpenStudio();
    check(alerts.length===1,'Saving without teacher review is blocked by the existing form handler');
    fields.newInterventionReviewed.checked=true;context.createStepLessonPlanAndOpenStudio();await new Promise(setImmediate);
    check(window.location.href==='/Signedd/public/iep/implementation/lesson/901/builder','Reviewed builder draft opens existing Slide Builder route');
    const saved=requests.at(-1).body;
    check(saved.intervention_draft&&saved.draft_reviewed&&saved.resource_draft.learning_competency==='addition','Existing create endpoint receives reviewed draft metadata');
    check(!Object.keys(saved.resource_draft).some(key=>key.includes('student')||key.includes('iep')),'Shared metadata contains no learner identifiers');
    window.openInterventionDraft(19,'upload');fields.newInterventionReviewed.checked=true;
    context.createStepLessonPlanAndOpenStudio();await new Promise(setImmediate);
    check(window.location.href==='/Signedd/public/iep/implementation/workspace/8?intervention_upload=901','Reviewed upload draft returns to existing workspace material upload flow');
    fields['intervention-ideas-text-19'].value='';
    fields['intervention-19'].dataset.interventionDraft=JSON.stringify({title:'New intervention',learning_competency:'',tags:'',description:'',pdsp_domain:'communication_language',requires_target_skill:true});
    for(const mode of ['builder','upload']){
        window.openInterventionDraft(19,mode);
        check(fields.newInterventionSkill.value===''&&fields.newInterventionTags.value===''&&!fields.interventionTargetSkillNotice.hidden,'Unverified '+mode+' creation starts blank and asks teacher to choose skill');
        fields.newInterventionReviewed.checked=true;
        const requestCount=requests.length;context.createStepLessonPlanAndOpenStudio();
        check(requests.length===requestCount,'Missing target skill blocks '+mode+' saving');
        fields.newInterventionSkill.value='teacher-selected reading skill';fields.newInterventionReviewed.checked=true;
        context.createStepLessonPlanAndOpenStudio();await new Promise(setImmediate);
        check(requests.at(-1).body.resource_draft.learning_competency==='teacher-selected reading skill','Teacher selection survives '+mode+' handoff without invented competency');
    }
    console.log(`Learning intervention UI: ${checks} checks passed; no browser or network used.`);
}
main().catch(error=>{console.error(error);process.exitCode=1;});
