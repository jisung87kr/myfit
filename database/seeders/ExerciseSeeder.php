<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $exercises = [
            // 유산소 (15개)
            ['name' => '걷기', 'category' => '유산소', 'intensity' => '낮음', 'met_value' => 3.5, 'calories_per_hour_per_kg' => 3.5, 'description' => '천천히 평지를 걷기'],
            ['name' => '빠르게 걷기', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 5.0, 'calories_per_hour_per_kg' => 5.0, 'description' => '빠른 속도로 평지를 걷기'],
            ['name' => '조깅', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 7.0, 'calories_per_hour_per_kg' => 7.0, 'description' => '천천히 달리기 (시속 8km)'],
            ['name' => '달리기', 'category' => '유산소', 'intensity' => '높음', 'met_value' => 10.0, 'calories_per_hour_per_kg' => 10.0, 'description' => '빠르게 달리기 (시속 10-12km)'],
            ['name' => '사이클링', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 6.8, 'calories_per_hour_per_kg' => 6.8, 'description' => '평지에서 보통 속도로 자전거 타기'],
            ['name' => '실내 자전거', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 7.0, 'calories_per_hour_per_kg' => 7.0, 'description' => '고정 자전거 중간 강도'],
            ['name' => '줄넘기', 'category' => '유산소', 'intensity' => '높음', 'met_value' => 12.3, 'calories_per_hour_per_kg' => 12.3, 'description' => '빠른 속도로 줄넘기'],
            ['name' => '계단 오르기', 'category' => '유산소', 'intensity' => '높음', 'met_value' => 8.8, 'calories_per_hour_per_kg' => 8.8, 'description' => '계단을 빠르게 오르기'],
            ['name' => '등산', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 6.5, 'calories_per_hour_per_kg' => 6.5, 'description' => '산 오르기, 가벼운 배낭'],
            ['name' => '수영 (자유형)', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 8.0, 'calories_per_hour_per_kg' => 8.0, 'description' => '중간 속도 자유형 수영'],
            ['name' => '수영 (평영)', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 10.0, 'calories_per_hour_per_kg' => 10.0, 'description' => '평영 수영'],
            ['name' => '수영 (배영)', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 7.0, 'calories_per_hour_per_kg' => 7.0, 'description' => '배영 수영'],
            ['name' => '에어로빅', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 7.3, 'calories_per_hour_per_kg' => 7.3, 'description' => '중간 강도 에어로빅'],
            ['name' => '댄스', 'category' => '유산소', 'intensity' => '보통', 'met_value' => 5.5, 'calories_per_hour_per_kg' => 5.5, 'description' => '일반적인 댄스'],
            ['name' => '스피닝', 'category' => '유산소', 'intensity' => '높음', 'met_value' => 8.5, 'calories_per_hour_per_kg' => 8.5, 'description' => '고강도 실내 사이클'],

            // 근력 (20개)
            ['name' => '푸쉬업', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.8, 'calories_per_hour_per_kg' => 3.8, 'description' => '표준 푸쉬업'],
            ['name' => '풀업', 'category' => '근력', 'intensity' => '높음', 'met_value' => 8.0, 'calories_per_hour_per_kg' => 8.0, 'description' => '턱걸이'],
            ['name' => '스쿼트', 'category' => '근력', 'intensity' => '보통', 'met_value' => 5.0, 'calories_per_hour_per_kg' => 5.0, 'description' => '맨몸 스쿼트'],
            ['name' => '런지', 'category' => '근력', 'intensity' => '보통', 'met_value' => 4.0, 'calories_per_hour_per_kg' => 4.0, 'description' => '정적 런지'],
            ['name' => '플랭크', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.8, 'calories_per_hour_per_kg' => 3.8, 'description' => '정적 플랭크 자세'],
            ['name' => '크런치', 'category' => '근력', 'intensity' => '낮음', 'met_value' => 2.8, 'calories_per_hour_per_kg' => 2.8, 'description' => '복근 운동'],
            ['name' => '싯업', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.8, 'calories_per_hour_per_kg' => 3.8, 'description' => '윗몸 일으키기'],
            ['name' => '버피', 'category' => '근력', 'intensity' => '높음', 'met_value' => 8.0, 'calories_per_hour_per_kg' => 8.0, 'description' => '전신 운동, 버피 테스트'],
            ['name' => '마운틴 클라이머', 'category' => '근력', 'intensity' => '높음', 'met_value' => 8.0, 'calories_per_hour_per_kg' => 8.0, 'description' => '빠른 마운틴 클라이머'],
            ['name' => '덤벨 컬', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.5, 'calories_per_hour_per_kg' => 3.5, 'description' => '이두 근육 운동'],
            ['name' => '벤치프레스', 'category' => '근력', 'intensity' => '보통', 'met_value' => 5.0, 'calories_per_hour_per_kg' => 5.0, 'description' => '가슴 근육 운동'],
            ['name' => '데드리프트', 'category' => '근력', 'intensity' => '높음', 'met_value' => 6.0, 'calories_per_hour_per_kg' => 6.0, 'description' => '전신 복합 운동'],
            ['name' => '숄더 프레스', 'category' => '근력', 'intensity' => '보통', 'met_value' => 4.0, 'calories_per_hour_per_kg' => 4.0, 'description' => '어깨 근육 운동'],
            ['name' => '레그 프레스', 'category' => '근력', 'intensity' => '보통', 'met_value' => 5.0, 'calories_per_hour_per_kg' => 5.0, 'description' => '다리 근육 운동'],
            ['name' => '레그 익스텐션', 'category' => '근력', 'intensity' => '낮음', 'met_value' => 2.5, 'calories_per_hour_per_kg' => 2.5, 'description' => '대퇴 사두근 운동'],
            ['name' => '레그 컬', 'category' => '근력', 'intensity' => '낮음', 'met_value' => 2.5, 'calories_per_hour_per_kg' => 2.5, 'description' => '햄스트링 운동'],
            ['name' => '사이드 플랭크', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.8, 'calories_per_hour_per_kg' => 3.8, 'description' => '옆구리 복근 운동'],
            ['name' => '딥스', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.8, 'calories_per_hour_per_kg' => 3.8, 'description' => '삼두근 운동'],
            ['name' => '케이블 크로스오버', 'category' => '근력', 'intensity' => '보통', 'met_value' => 3.5, 'calories_per_hour_per_kg' => 3.5, 'description' => '가슴 근육 케이블 운동'],
            ['name' => '하이퍼 익스텐션', 'category' => '근력', 'intensity' => '낮음', 'met_value' => 2.8, 'calories_per_hour_per_kg' => 2.8, 'description' => '허리 근육 운동'],

            // 스트레칭 (10개)
            ['name' => '요가 (하타)', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 2.5, 'calories_per_hour_per_kg' => 2.5, 'description' => '기본 하타 요가'],
            ['name' => '요가 (파워)', 'category' => '스트레칭', 'intensity' => '보통', 'met_value' => 4.0, 'calories_per_hour_per_kg' => 4.0, 'description' => '파워 요가, 빈야사'],
            ['name' => '필라테스', 'category' => '스트레칭', 'intensity' => '보통', 'met_value' => 3.0, 'calories_per_hour_per_kg' => 3.0, 'description' => '일반 필라테스'],
            ['name' => '정적 스트레칭', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 2.3, 'calories_per_hour_per_kg' => 2.3, 'description' => '정적 근육 스트레칭'],
            ['name' => '동적 스트레칭', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 2.8, 'calories_per_hour_per_kg' => 2.8, 'description' => '동적 워밍업 스트레칭'],
            ['name' => '폼롤링', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 2.3, 'calories_per_hour_per_kg' => 2.3, 'description' => '폼롤러를 이용한 근막 이완'],
            ['name' => '태극권', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 3.0, 'calories_per_hour_per_kg' => 3.0, 'description' => '태극권 수련'],
            ['name' => '바디펌프', 'category' => '스트레칭', 'intensity' => '보통', 'met_value' => 5.0, 'calories_per_hour_per_kg' => 5.0, 'description' => '바벨을 이용한 전신 운동'],
            ['name' => '스트레칭 밴드', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 2.5, 'calories_per_hour_per_kg' => 2.5, 'description' => '저항 밴드 스트레칭'],
            ['name' => '아침 스트레칭', 'category' => '스트레칭', 'intensity' => '낮음', 'met_value' => 2.0, 'calories_per_hour_per_kg' => 2.0, 'description' => '가벼운 전신 스트레칭'],

            // 스포츠 (5개)
            ['name' => '축구', 'category' => '스포츠', 'intensity' => '높음', 'met_value' => 10.0, 'calories_per_hour_per_kg' => 10.0, 'description' => '경쟁적인 축구 경기'],
            ['name' => '농구', 'category' => '스포츠', 'intensity' => '높음', 'met_value' => 8.0, 'calories_per_hour_per_kg' => 8.0, 'description' => '경쟁적인 농구 경기'],
            ['name' => '테니스', 'category' => '스포츠', 'intensity' => '보통', 'met_value' => 7.3, 'calories_per_hour_per_kg' => 7.3, 'description' => '단식 테니스'],
            ['name' => '배드민턴', 'category' => '스포츠', 'intensity' => '보통', 'met_value' => 5.5, 'calories_per_hour_per_kg' => 5.5, 'description' => '경쟁적인 배드민턴'],
            ['name' => '탁구', 'category' => '스포츠', 'intensity' => '보통', 'met_value' => 4.0, 'calories_per_hour_per_kg' => 4.0, 'description' => '경쟁적인 탁구'],
        ];

        foreach ($exercises as $exercise) {
            Exercise::create($exercise);
        }
    }
}
