<?php
declare(strict_types=1);
ob_start();require __DIR__.'/ResourceTagWorkflowTest.php';ob_end_clean();
$checks=0;
function dedupCheck(bool $ok,string $message):void{global$checks;$checks++;if(!$ok)throw new RuntimeException($message);}
$db=new TagWorkflowDatabase();
$db->activity['title']='FSL family quiz';$db->activity['activity_data']='{}';
$db->log['selected_value']='Doctor';$db->log['correct_value']='Nanay (Mother)';
$page=['title'=>'Ang Aking Pamilya','content'=>'<span data-word="mother">Ina / Nanay</span>','guide_questions'=>'Practice family signs','media_type'=>'none','media_path'=>null];
$activity=['title'=>'School quiz','activity_type'=>'multiple_choice','instructions'=>'Choose an answer.','max_score'=>5,'is_f2f'=>0,
    'activity_data'=>'{"questions":[{"question":"Who teaches the class?","options":[{"text":"Teacher (Guro)","is_correct":1},{"text":"Doctor"}]}]}'];
foreach([3,4,5]as$id){
    $db->packs[$id]=['id'=>$id,'title'=>'Test Shared Addition Pack','description'=>'','status'=>'published','tags'=>'','learning_competency'=>'','pdsp_domain'=>'communication_language','learning_level'=>'Primary','fsl_supported'=>1,'source_lesson_plan_id'=>20];
    $db->pages[]=array_merge($page,['id'=>100+$id,'pack_id'=>$id]);
    $db->activities[]=array_merge($activity,['id'=>200+$id,'pack_id'=>$id]);
}
$service=new ResourceRecommendationService($db,new LearningGapAnalyzer($db));
$result=$service->recommendForActivity(6,6);
$hub=fn($r)=>array_values(array_filter($r['recommendations'],fn($rec)=>$rec['resource_type']==='resource_hub_pack'));
dedupCheck(array_column($hub($result),'source_id')===[3],'Three identical published copies produce one deterministic Hub suggestion');
dedupCheck(in_array(97,array_column($result['recommendations'],'source_id'),true),'Distinct FSL dictionary video remains available');
dedupCheck($result['recommendations_count']===2 && str_contains($result['explanation'],'2 relevant resource'),'Counts and explanation reflect unique suggestions');
dedupCheck(!array_filter($result['recommendations'],fn($r)=>isset($r['_content_fingerprint'])),'Internal comparison keys do not leak into response');
$db->packs[4]['title']='Renamed family pack copy';
dedupCheck(count($hub($service->recommendForActivity(6,6)))===1,'Different pack labels do not disguise identical instructional snapshots');
$db->packs[6]=array_merge($db->packs[3],['id'=>6]);
$db->pages[]=array_merge($page,['id'=>106,'pack_id'=>6,'content'=>'<span data-word="mother">Mother practice with a different example.</span>']);
$db->activities[]=array_merge($activity,['id'=>206,'pack_id'=>6]);
dedupCheck(array_column($hub($service->recommendForActivity(6,6)),'source_id')===[3,6],'Different content remains distinct even with the same title and source lesson');
$learningInsights=[$result];$basePath='/Signedd/public';
ob_start();require __DIR__.'/../app/Views/iep_implementation/tab_learning_insights.php';$html=ob_get_clean();
dedupCheck(substr_count($html,'>Preview Resource</a>')===1 && str_contains($html,'/resource-hub/preview/3'),'One working preview points at the retained eligible pack');
dedupCheck(!$db->writes && count($db->packs)===4,'Filtering suggestions never deletes or updates stored packs');
$first=ResourceHubContentMatcher::fingerprint([$page],[$activity]);
dedupCheck($first===ResourceHubContentMatcher::fingerprint([array_merge($page,['id'=>55,'pack_id'=>99])],[array_merge($activity,['id'=>88,'pack_id'=>99])]),'Snapshot identity ignores storage IDs');
dedupCheck($first!==ResourceHubContentMatcher::fingerprint([$page],[array_merge($activity,['instructions'=>'Use a different practice procedure.'])]),'Changed activity instructions remain distinct');
dedupCheck(ResourceHubContentMatcher::fingerprint([array_merge($page,['media_path'=>'a.png'])],[])!==ResourceHubContentMatcher::fingerprint([array_merge($page,['media_path'=>'b.png'])],[]),'Different media assets are not collapsed');
$jsonA=array_merge($activity,['activity_data'=>'{"questions":[{"question":"Q","options":["A","B"],"correct":0}]}']);
$jsonB=array_merge($activity,['activity_data'=>'{"questions":[{"correct":0,"options":["A","B"],"question":"Q"}]}']);
dedupCheck(ResourceHubContentMatcher::fingerprint([],[$jsonA])===ResourceHubContentMatcher::fingerprint([],[$jsonB]),'Equivalent JSON object key order does not create duplicate suggestions');
$jsonB['activity_data']='{"questions":[{"correct":0,"options":["B","A"],"question":"Q"}]}';
dedupCheck(ResourceHubContentMatcher::fingerprint([],[$jsonA])!==ResourceHubContentMatcher::fingerprint([],[$jsonB]),'Different answer option order is preserved');
echo "Recommendation content deduplication: {$checks} checks passed; no database connection or network used.\n";
