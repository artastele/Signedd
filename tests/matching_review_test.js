'use strict';
const fs = require('node:fs'), vm = require('node:vm'), assert = require('node:assert/strict');
const source = fs.readFileSync('app/Views/learning/activity_play.php','utf8');
function fn(name) {
    const start = source.indexOf('function ' + name + '(');
    const end = source.indexOf('\nfunction ', start + 1);
    assert.ok(start > 0 && end > start, name + ' found');
    return source.slice(start, end);
}
function fixture() {
    const element = (index = 0) => ({dataset:{index:String(index)},value:'selected',style:{},disabled:false,classList:{add(){},remove(){}}});
    const left = [0,1,2].map(element), right = [0,1,2].map(element);
    const fields = {missionReviewList:{innerHTML:''}, frontendScorePreview:{textContent:''}, saveMatchingBtn_0:element()};
    const events = [];
    const card = {querySelectorAll:selector => selector === '.left-card' ? left : []};
    const context = vm.createContext({
        TOTAL_STEPS:1,ACTIVITY_TYPE:'matching',currentStep:0,
        checkedSteps:{}, frontendResults:{}, attemptCounts:{},
        matchingConnections:{0:{0:{rightText:'Blue',rightIdx:0},1:{rightText:'Pula',rightIdx:1},2:{rightText:'Dilaw',rightIdx:2}}},
        FEEDBACK_DATA:{matching:[['Dilaw','Pula','Blue']]},
        document:{
            getElementById:id => fields[id] || (id==='matchingCard_0'?card:element()),
            querySelectorAll:selector => selector.includes('.left-card') ? left : selector.includes('.right-card') ? right : selector==='.matching-select' ? left : [],
        },
        getCardForStep:() => card,
        getSelectedAnswerText:() => 'Pair 1: Blue; Pair 2: Pula; Pair 3: Dilaw',
        getQuestionTitle:() => 'Matching Set 1',
        setFeedback:(...args)=>events.push(args),playAudioTone(){},triggerShakeCard(){},labelCard(){},lockStep(){},updateGameStage(){},autoAdvance:()=>events.push('advance'),
        Swal:{fire(){throw Error('Unexpected alert');}},
    });
    vm.runInContext(['getItemAccuracy','calculateOverallAccuracy','isStepAnswered','goChallenge','confirmMatchingStep','validateAllAnswered','buildMissionReview','escapeHtml'].map(fn).join('\n'),context);
    return {context,fields,events,left};
}
const {context:c,fields,events} = fixture();
assert.equal(c.calculateOverallAccuracy(),null,'Unchecked matching has no accuracy');
c.buildMissionReview();
assert.ok(fields.missionReviewList.innerHTML.includes('Pending check'));
assert.ok(!fields.missionReviewList.innerHTML.includes('100%'),'Review never invents a perfect score');
assert.ok(fields.frontendScorePreview.textContent.includes('not been checked'));
assert.equal(c.validateAllAnswered(),false,'Selections alone cannot submit as checked answers');
c.goChallenge(1);
assert.equal(c.currentStep,0,'Next cannot bypass checking deliberately wrong matching pairs');
assert.equal(c.attemptCounts[0],1);
assert.equal(c.frontendResults[0].status,'wrong');
assert.ok(!events.includes('advance'));
c.buildMissionReview();
assert.ok(fields.missionReviewList.innerHTML.includes('Needs practice'));
assert.ok(!fields.frontendScorePreview.textContent.includes('All challenges'));
assert.equal(c.calculateOverallAccuracy(),0);
c.matchingConnections[0]={0:{rightText:'Dilaw',rightIdx:2},1:{rightText:'Pula',rightIdx:1},2:{rightText:'Blue',rightIdx:0}};
c.goChallenge(1);
assert.equal(c.checkedSteps[0],true);
assert.equal(c.attemptCounts[0],2);
assert.ok(events.includes('advance'),'Correct answer can advance');
assert.equal(c.calculateOverallAccuracy(),50,'One failed check followed by success is 50% practice accuracy');
assert.equal(c.validateAllAnswered(),true);
c.buildMissionReview();
assert.ok(fields.frontendScorePreview.textContent.includes('Practice accuracy: 50%'));
assert.equal(c.getItemAccuracy(0),0);
assert.equal(c.getItemAccuracy(3),33.33);
const four=fixture();four.context.TOTAL_STEPS=4;
four.context.frontendResults={0:{status:'correct'},1:{status:'correct'},2:{status:'correct'},3:{status:'correct'}};
four.context.attemptCounts={0:12,1:5,2:5,3:1};
assert.equal(four.context.calculateOverallAccuracy(),17.39,'Four successful checks across 23 attempts use the combined ratio');
const empty=fixture();empty.left.length=0;
assert.equal(empty.context.isStepAnswered(0),false,'Missing matching DOM never counts as completed');
empty.context.confirmMatchingStep(0);
assert.notEqual(empty.context.checkedSteps[0],true,'Empty card list never grades as correct');
console.log('PASS: Actual matching Next/check/review handlers reject wrong or unchecked pairs; retry accuracy and submission readiness remain consistent.');
