<?php
declare(strict_types=1);
// Reuse isolated PDO fixtures and the real service/publisher/view. Never connects to MySQL.
require __DIR__ . '/ResourceTagWorkflowTest.php';
require_once __DIR__ . '/../app/Services/GeminiExplanationService.php';
$interventionChecks = 0;
function interventionCheck(bool $ok, string $message): void {
    global $interventionChecks; $interventionChecks++;
    if (!$ok) throw new RuntimeException($message);
}
$db = new TagWorkflowDatabase();
$db->activity['id'] = 19; $db->activity['title'] = 'SignED Warm-up: Pagsasama ng Bilang';
$db->activity['activity_data'] = '{"questions":[{"question":"Ilan ang 3 + 2?","options":["4","5","6"],"correct":1,"points":10}]}';
$db->attempt['score'] = 0; $db->log['selected_value'] = '4'; $db->log['correct_value'] = '';
$db->packs = [
    7=>['id'=>7,'title'=>'Mathematics: Basic Addition within 10 (Accessibility Pack)','description'=>'','status'=>'published','tags'=>'math, addition','learning_competency'=>'','pdsp_domain'=>'perceptuo_cognitive','learning_level'=>'Primary','fsl_supported'=>1],
    3=>['id'=>3,'title'=>'Test Shared Addition Pack','description'=>'','status'=>'published','tags'=>'','learning_competency'=>'','pdsp_domain'=>'perceptuo_cognitive','learning_level'=>'Primary','fsl_supported'=>1],
];
$db->pages = [['id'=>16,'pack_id'=>7,'title'=>'Panimula sa Pagdaragdag','content'=>'Ang pagdaragdag ay pagsasama ng mga bagay.'],
    ['id'=>4,'pack_id'=>3,'title'=>'Ang Aking Pamilya','content'=>'<span data-word="mother">Ina / Nanay</span>']];
$db->activities = [
    ['id'=>5,'pack_id'=>7,'title'=>'Addition warm-up','activity_data'=>$db->activity['activity_data']],
    ['id'=>1,'pack_id'=>3,'title'=>'School quiz','activity_data'=>'{"questions":[{"question":"Who teaches the class?","options":[{"text":"Teacher (Guro)","is_correct":1},{"text":"Doctor"}]}]}'],
];
$analyzer = new LearningGapAnalyzer($db); $service = new ResourceRecommendationService($db,$analyzer);
$insight = $service->recommendForActivity(5,19);
interventionCheck(!$insight['recommendations'] && !$insight['intervention']['actionable'], 'Leo legacy empty expected answer cannot trigger interventions');
interventionCheck(str_contains(implode(' ',$insight['limitations']),'missing expected'), 'Historical evidence limitation is visible');
$db->log['correct_value'] = '5';
$db->attempt['answers'] = json_encode([0=>0,'_evidence_v1'=>[0=>['prompt'=>'Ilan ang 3 + 2?','selected'=>'4','expected'=>'5','correct'=>false]]]);
$valid = $service->recommendForActivity(5,19);
interventionCheck(array_column($valid['recommendations'],'source_id') === [7], 'Valid new addition evidence matches real-shaped Pack 7 content, rejects misleading addition pack 3');
interventionCheck($valid['recommendations'][0]['match_rank'] === 100, 'Exact captured question ranks above weaker matches');
interventionCheck($valid['intervention']['actionable'], 'Specific valid difficulty offers creation with existing matches');
$learningInsights=[$valid]; $basePath='/Signedd/public';
ob_start();require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php';$view=ob_get_clean();
interventionCheck(str_contains($view,'/resource-hub/preview/7') && str_contains($view,'Use Existing Resource'), 'Preview uses correct existing pack route');
interventionCheck(str_contains($view,'Create Intervention') && str_contains($view,'Create with Slide Builder') && str_contains($view,'Upload Material'), 'Both creation actions appear independently of existing resources');
$db->packs[7]['status']='archived';
$without = $service->recommendForActivity(5,19);
interventionCheck(!$without['recommendations'] && $without['intervention']['actionable'], 'Creation remains available without eligible resources');
$learningInsights=[$without];ob_start();require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php';$view=ob_get_clean();
interventionCheck(str_contains($view,'Create Intervention') && !str_contains($view,'Use Existing Resource'), 'No-match UI still offers creation');
$db->packs[7]['status']='published';
foreach (['Nanay (Mother)','Teacher (Guro)'] as $expected) {
    $db->activity['title']='FSL quiz';$db->activity['activity_data']='{}';$db->attempt['score']=6;
    $db->attempt['answers']='{}';$db->log['correct_value']=$expected;$db->log['selected_value']=$expected;
    $legacy = $service->recommendForActivity(6,6);
    interventionCheck(!$legacy['recommendations'] && !$legacy['intervention']['actionable'], 'Contradictory Maya log is excluded: '.$expected);
    interventionCheck($legacy['intervention']['learning_competency']==='' && $legacy['intervention']['tags']==='', 'Unverified Maya result invents no creation defaults');
    $learningInsights=[$legacy];ob_start();require __DIR__ . '/../app/Views/iep_implementation/tab_learning_insights.php';$legacyView=ob_get_clean();
    interventionCheck(str_contains($legacyView,'Needs further assessment') && str_contains($legacyView,'cannot yet identify the exact skill'), 'Maya low score is explained without a fabricated gap');
    interventionCheck(str_contains($legacyView,'Browse Existing Resources') && str_contains($legacyView,'Create Intervention'), 'Ambiguous Maya result still provides browse and creation');
    interventionCheck(strpos($legacyView,'Ambiguous historical item evidence')>strpos($legacyView,'<details'), 'Technical warnings remain inside evidence details');
    interventionCheck(!str_contains(substr($legacyView,0,strpos($legacyView,'<details')),'Insufficient reliable item evidence') && !str_contains($legacyView,'No matching resource is available'), 'Main teacher summary avoids the confusing warning combination');
    interventionCheck(!str_contains($legacyView,'>Suggest Intervention Ideas</button>'), 'Automatic AI advice is not offered for unverified evidence');
    $db->log['selected_value']='Doctor';
    $fresh = $service->recommendForActivity(6,6);
    interventionCheck(in_array(3,array_column($fresh['recommendations'],'source_id'),true), 'Verified concept matches Hub activity/slide despite misleading pack title: '.$expected);
}
$pack=$db->packs[3];
$noise=[['id'=>99,'title'=>'Story','content'=>'Please ask your teacher or mother for help.']];
interventionCheck(ResourceHubContentMatcher::rank($pack,$noise,[],[['expected'=>'Teacher (Guro)']],['teacher'])===null, 'Incidental teacher word does not match a resource');
interventionCheck(ResourceHubContentMatcher::rank($pack,[],[['id'=>555,'activity_data'=>'{"questions":"invalid"}']], [['expected'=>'Teacher (Guro)']],['teacher'])===null, 'Malformed Hub content is ignored without inventing a match');
$mathWrong=[['id'=>88,'activity_data'=>'{"questions":[{"question":"Ilan ang 3 - 2?","options":["1","5"],"correct":0}]}']];
interventionCheck(ResourceHubContentMatcher::rank($db->packs[7],[],$mathWrong,[['expected'=>'5','prompt'=>'Ilan ang 3 + 2?']],['addition'])===null, 'Different operation/question cannot match merely because numbers overlap');
$db->attempt['score']=10;$db->log['is_correct']=1;
$followup=$service->recommendForActivity(6,6);
interventionCheck(!$followup['intervention']['actionable'] && !$followup['recommendations'], 'Newer correct performance clears active intervention suggestions');
interventionCheck(str_contains($view,'do not establish that a particular intervention caused improvement'), 'Follow-up text avoids causal claims');
$q=['options'=>['4','5','6'],'correct'=>1];
foreach ([[0,'4',false],[1,'5',true],['4','4',false],['5','5',true],[null,'',false]] as [$answer,$selected,$correct]) {
    $evaluated=ActivityEvidence::multipleChoice($q,$answer);
    interventionCheck($evaluated['expected']==='5' && $evaluated['selected']===$selected && $evaluated['correct']===$correct, 'New scoring records normalized selected and expected values');
}
$dup=ActivityEvidence::multipleChoice(['options'=>[['text'=>'Apple'],['text'=>'Apple','is_correct'=>1]]],0);
interventionCheck($dup['selected']!==$dup['expected'] && !$dup['correct'], 'Duplicate labels retain option identity');
interventionCheck(ActivityEvidence::booleanAnswer(false)==='false' && ActivityEvidence::booleanAnswer('TRUE')==='true', 'Boolean representations normalize consistently');
class InterventionGeminiFixture extends GeminiExplanationService {
    public string $prompt=''; public string $mode='ok';
    public function __construct(bool $key=true) {
        (new ReflectionProperty(GeminiExplanationService::class,'apiKey'))->setValue($this,$key ? 'fixture-key' : null);
    }
    protected function callGemini(string $prompt): string {
        $this->prompt=$prompt;
        if ($this->mode==='unavailable') throw new RuntimeException('Offline');
        $idea=$this->mode==='invented' ? 'Use Resource Pack #999 at https://example.com' : 'Use counters to model addition, then offer guided practice.';
        return json_encode(['candidates'=>[['content'=>['parts'=>[['text'=>json_encode(['ideas'=>[$idea]])]]]]]]);
    }
}
$ai=new InterventionGeminiFixture();
interventionCheck($ai->suggestInterventionIdeas('addition')['source']==='gemini', 'Optional Gemini ideas reuse existing transport');
interventionCheck(!str_contains($ai->prompt,'student_id') && !str_contains($ai->prompt,'Leo') && !str_contains($ai->prompt,'IEP'), 'Gemini receives only standardized instructional concept');
$ai->mode='unavailable';interventionCheck($ai->suggestInterventionIdeas('addition')['source']==='local_rules', 'Gemini failure provides rule-based ideas');
$ai->mode='invented';interventionCheck($ai->suggestInterventionIdeas('addition')['source']==='local_rules', 'Invented resource references are rejected');
interventionCheck((new InterventionGeminiFixture(false))->suggestInterventionIdeas('addition')['source']==='local_rules', 'Missing Gemini key uses fallback');
$before=$ai->prompt;$ai->suggestInterventionIdeas('Leo Ramos private details');
interventionCheck($before===$ai->prompt, 'Nonstandard raw context is not transmitted');
echo "Intervention integration: {$interventionChecks} additional checks passed; no real records or network used.\n";
