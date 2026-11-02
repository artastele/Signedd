<?php

/**
 * Read-only dashboard metrics. Evidence presence is not an academic validation.
 * Retains the existing four-criterion passing and COT eligibility rules until
 * individual thresholds and acceptable alternative verification are approved.
 * No period filter is added: these are explicitly cumulative metrics.
 */
class ObjectiveMetricsModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    private function count(string $sql, int $schoolId): int
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['sid' => $schoolId]);
        return (int) $stmt->fetchColumn();
    }

    public function forSchool(array $school): array
    {
        $id = (int) $school['id'];
        $e = [];
        $e['plans'] = $this->count("SELECT COUNT(*) FROM lesson_plans lp JOIN users u ON u.id=lp.created_by WHERE u.school_id=:sid AND lp.status='published'", $id);
        $e['dll'] = $this->count("SELECT COUNT(*) FROM traditional_iep_documents d JOIN student_records sr ON sr.id=d.student_id WHERE sr.school_id=:sid AND d.document_type='dll'", $id);
        $e['classroom_finalized'] = $this->count("SELECT COUNT(*) FROM classroom_observations co JOIN users u ON u.id=co.observed_teacher_id WHERE u.school_id=:sid AND co.status='finalized'", $id);
        $e['legacy_finalized'] = $this->count("SELECT COUNT(*) FROM cot_observations co JOIN users u ON u.id=co.observed_teacher_id WHERE u.school_id=:sid AND co.status='finalized'", $id);
        $e['proficient'] = $this->count("SELECT COUNT(*) FROM classroom_observations co JOIN users u ON u.id=co.observed_teacher_id WHERE u.school_id=:sid AND co.status='finalized' AND (co.average_score IS NULL OR co.average_score>=3.0)", $id);
        $e['resources'] = $this->count("SELECT COUNT(*) FROM learning_materials lm JOIN users u ON u.id=lm.uploaded_by WHERE u.school_id=:sid", $id);
        $e['certificates'] = $this->count("SELECT COUNT(*) FROM users WHERE school_id=:sid AND role IN ('sped_teacher','guidance','master_teacher','general_teacher') AND fsl_cert_path IS NOT NULL AND fsl_cert_path!=''", $id);
        $e['signed_ieps'] = $this->count("SELECT COUNT(*) FROM iep_records ir JOIN student_records sr ON sr.id=ir.student_id WHERE sr.school_id=:sid AND ir.status='signed'", $id);
        $e['sip'] = !empty($school['sip_path']);
        return self::calculate($e);
    }

    public static function calculate(array $e): array
    {
        $plans = $e['plans'] > 0 || $e['dll'] > 0;
        $cot = $e['classroom_finalized'] + $e['legacy_finalized'] > 0;
        // Retain existing resource and certificate proxies; do not call them verified.
        $resources = $e['resources'] > 0 || $e['plans'] > 0;
        $cert = $e['certificates'] > 0;
        $points = [
            'plans' => $plans ? 12.5 : 0,
            'cot' => $cot ? 12.5 : 0,
            'resources' => $resources ? 25 : 0,
            'faculty' => $cert ? 25 : 0,
            'sip' => $e['sip'] ? 25 : 0,
        ];
        $so1Criteria = [$plans && $cot, $resources, $cert, (bool) $e['sip']];
        $so2Criteria = [$e['plans'] > 0, $e['signed_ieps'] > 0, $cert, $e['proficient'] > 0];
        $result = ['evidence' => $e, 'points' => $points];
        foreach (['so1' => [$so1Criteria, $cot, array_sum($points)], 'so2' => [$so2Criteria, $e['proficient'] > 0, count(array_filter($so2Criteria)) * 25]] as $key => $data) {
            [$criteria, $evaluated, $score] = $data;
            $passed = $evaluated && !in_array(false, $criteria, true);
            $result[$key] = [
                'score' => $score, 'met' => count(array_filter($criteria)),
                'evaluated' => $evaluated, 'passed' => $passed,
                'status' => !$evaluated ? 'Pending Evidence' : ($passed ? ($key === 'so1' ? 'Compliant' : 'Adopted') : ($key === 'so1' ? 'Not compliant' : 'Not adopted')),
            ];
        }
        return $result;
    }

    public static function aggregate(array $schools, string $objective): array
    {
        $evaluated = $passed = 0;
        foreach ($schools as $metrics) {
            $evaluated += (int) $metrics[$objective]['evaluated'];
            $passed += (int) $metrics[$objective]['passed'];
        }
        return ['evaluated' => $evaluated, 'passed' => $passed,
            'pending' => count($schools) - $evaluated,
            'rate' => $evaluated ? round($passed / $evaluated * 100, 1) : null];
    }
}
