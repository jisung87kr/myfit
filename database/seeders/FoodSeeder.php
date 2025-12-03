<?php

namespace Database\Seeders;

use App\Models\Food;
use Illuminate\Database\Seeder;

class FoodSeeder extends Seeder
{
    public function run(): void
    {
        $foods = [
            // 곡류 (20개)
            ['name' => '백미밥', 'name_en' => 'White Rice', 'category' => '곡류', 'serving_size' => 210, 'calories' => 300, 'protein_g' => 5.3, 'carbs_g' => 66.3, 'fat_g' => 0.5, 'fiber_g' => 0.8, 'sodium_mg' => 0],
            ['name' => '현미밥', 'name_en' => 'Brown Rice', 'category' => '곡류', 'serving_size' => 210, 'calories' => 290, 'protein_g' => 6.5, 'carbs_g' => 60.5, 'fat_g' => 2.7, 'fiber_g' => 3.5, 'sodium_mg' => 0],
            ['name' => '식빵', 'name_en' => 'White Bread', 'category' => '곡류', 'serving_size' => 30, 'calories' => 80, 'protein_g' => 2.5, 'carbs_g' => 14.8, 'fat_g' => 1.0, 'fiber_g' => 0.8, 'sodium_mg' => 160],
            ['name' => '통밀빵', 'name_en' => 'Whole Wheat Bread', 'category' => '곡류', 'serving_size' => 30, 'calories' => 75, 'protein_g' => 3.5, 'carbs_g' => 13.0, 'fat_g' => 1.2, 'fiber_g' => 2.5, 'sodium_mg' => 150],
            ['name' => '라면', 'name_en' => 'Instant Noodles', 'category' => '곡류', 'serving_size' => 120, 'calories' => 500, 'protein_g' => 10.0, 'carbs_g' => 70.0, 'fat_g' => 18.0, 'fiber_g' => 2.0, 'sodium_mg' => 1800],
            ['name' => '우동', 'name_en' => 'Udon', 'category' => '곡류', 'serving_size' => 200, 'calories' => 260, 'protein_g' => 6.8, 'carbs_g' => 55.0, 'fat_g' => 0.8, 'fiber_g' => 2.4, 'sodium_mg' => 800],
            ['name' => '스파게티면', 'name_en' => 'Spaghetti', 'category' => '곡류', 'serving_size' => 100, 'calories' => 370, 'protein_g' => 13.0, 'carbs_g' => 75.0, 'fat_g' => 1.5, 'fiber_g' => 3.0, 'sodium_mg' => 5],
            ['name' => '고구마', 'name_en' => 'Sweet Potato', 'category' => '곡류', 'serving_size' => 200, 'calories' => 172, 'protein_g' => 2.4, 'carbs_g' => 39.2, 'fat_g' => 0.2, 'fiber_g' => 4.8, 'sodium_mg' => 72],
            ['name' => '감자', 'name_en' => 'Potato', 'category' => '곡류', 'serving_size' => 150, 'calories' => 116, 'protein_g' => 3.0, 'carbs_g' => 26.2, 'fat_g' => 0.2, 'fiber_g' => 2.9, 'sodium_mg' => 9],
            ['name' => '옥수수', 'name_en' => 'Corn', 'category' => '곡류', 'serving_size' => 100, 'calories' => 96, 'protein_g' => 3.4, 'carbs_g' => 21.0, 'fat_g' => 1.5, 'fiber_g' => 2.4, 'sodium_mg' => 15],
            ['name' => '오트밀', 'name_en' => 'Oatmeal', 'category' => '곡류', 'serving_size' => 40, 'calories' => 150, 'protein_g' => 5.3, 'carbs_g' => 27.0, 'fat_g' => 2.5, 'fiber_g' => 4.0, 'sodium_mg' => 0],
            ['name' => '시리얼', 'name_en' => 'Cereal', 'category' => '곡류', 'serving_size' => 30, 'calories' => 110, 'protein_g' => 2.0, 'carbs_g' => 24.0, 'fat_g' => 1.0, 'fiber_g' => 3.0, 'sodium_mg' => 150],
            ['name' => '떡', 'name_en' => 'Rice Cake', 'category' => '곡류', 'serving_size' => 100, 'calories' => 235, 'protein_g' => 4.2, 'carbs_g' => 52.3, 'fat_g' => 0.5, 'fiber_g' => 1.0, 'sodium_mg' => 5],
            ['name' => '식혜', 'name_en' => 'Sweet Rice Drink', 'category' => '음료', 'serving_size' => 200, 'calories' => 140, 'protein_g' => 1.0, 'carbs_g' => 34.0, 'fat_g' => 0.2, 'fiber_g' => 0.5, 'sodium_mg' => 10],
            ['name' => '누룽지', 'name_en' => 'Scorched Rice', 'category' => '곡류', 'serving_size' => 50, 'calories' => 180, 'protein_g' => 3.5, 'carbs_g' => 40.0, 'fat_g' => 0.5, 'fiber_g' => 1.2, 'sodium_mg' => 0],
            ['name' => '보리밥', 'name_en' => 'Barley Rice', 'category' => '곡류', 'serving_size' => 210, 'calories' => 280, 'protein_g' => 7.0, 'carbs_g' => 58.0, 'fat_g' => 1.5, 'fiber_g' => 5.0, 'sodium_mg' => 0],
            ['name' => '잡곡밥', 'name_en' => 'Mixed Grain Rice', 'category' => '곡류', 'serving_size' => 210, 'calories' => 285, 'protein_g' => 7.5, 'carbs_g' => 59.0, 'fat_g' => 2.0, 'fiber_g' => 4.5, 'sodium_mg' => 0],
            ['name' => '퀴노아', 'name_en' => 'Quinoa', 'category' => '곡류', 'serving_size' => 100, 'calories' => 368, 'protein_g' => 14.0, 'carbs_g' => 64.0, 'fat_g' => 6.0, 'fiber_g' => 7.0, 'sodium_mg' => 5],
            ['name' => '베이글', 'name_en' => 'Bagel', 'category' => '곡류', 'serving_size' => 90, 'calories' => 250, 'protein_g' => 10.0, 'carbs_g' => 49.0, 'fat_g' => 1.5, 'fiber_g' => 2.0, 'sodium_mg' => 450],
            ['name' => '크루아상', 'name_en' => 'Croissant', 'category' => '곡류', 'serving_size' => 60, 'calories' => 230, 'protein_g' => 4.5, 'carbs_g' => 26.0, 'fat_g' => 12.0, 'fiber_g' => 1.5, 'sodium_mg' => 270],

            // 단백질 (25개)
            ['name' => '닭가슴살', 'name_en' => 'Chicken Breast', 'category' => '단백질', 'serving_size' => 100, 'calories' => 165, 'protein_g' => 31.0, 'carbs_g' => 0, 'fat_g' => 3.6, 'fiber_g' => 0, 'sodium_mg' => 74],
            ['name' => '계란', 'name_en' => 'Egg', 'category' => '단백질', 'serving_size' => 50, 'calories' => 72, 'protein_g' => 6.3, 'carbs_g' => 0.4, 'fat_g' => 4.8, 'fiber_g' => 0, 'sodium_mg' => 71],
            ['name' => '소고기', 'name_en' => 'Beef', 'category' => '단백질', 'serving_size' => 100, 'calories' => 250, 'protein_g' => 26.0, 'carbs_g' => 0, 'fat_g' => 16.0, 'fiber_g' => 0, 'sodium_mg' => 60],
            ['name' => '돼지고기', 'name_en' => 'Pork', 'category' => '단백질', 'serving_size' => 100, 'calories' => 242, 'protein_g' => 27.0, 'carbs_g' => 0, 'fat_g' => 14.0, 'fiber_g' => 0, 'sodium_mg' => 62],
            ['name' => '두부', 'name_en' => 'Tofu', 'category' => '단백질', 'serving_size' => 80, 'calories' => 60, 'protein_g' => 6.6, 'carbs_g' => 1.9, 'fat_g' => 3.5, 'fiber_g' => 0.4, 'sodium_mg' => 7],
            ['name' => '연어', 'name_en' => 'Salmon', 'category' => '단백질', 'serving_size' => 100, 'calories' => 208, 'protein_g' => 20.0, 'carbs_g' => 0, 'fat_g' => 13.0, 'fiber_g' => 0, 'sodium_mg' => 59],
            ['name' => '참치캔', 'name_en' => 'Canned Tuna', 'category' => '단백질', 'serving_size' => 100, 'calories' => 116, 'protein_g' => 26.0, 'carbs_g' => 0, 'fat_g' => 0.8, 'fiber_g' => 0, 'sodium_mg' => 320],
            ['name' => '고등어', 'name_en' => 'Mackerel', 'category' => '단백질', 'serving_size' => 100, 'calories' => 205, 'protein_g' => 19.0, 'carbs_g' => 0, 'fat_g' => 14.0, 'fiber_g' => 0, 'sodium_mg' => 90],
            ['name' => '새우', 'name_en' => 'Shrimp', 'category' => '단백질', 'serving_size' => 100, 'calories' => 99, 'protein_g' => 24.0, 'carbs_g' => 0.2, 'fat_g' => 0.3, 'fiber_g' => 0, 'sodium_mg' => 111],
            ['name' => '오징어', 'name_en' => 'Squid', 'category' => '단백질', 'serving_size' => 100, 'calories' => 92, 'protein_g' => 15.6, 'carbs_g' => 3.1, 'fat_g' => 1.4, 'fiber_g' => 0, 'sodium_mg' => 44],
            ['name' => '콩', 'name_en' => 'Beans', 'category' => '단백질', 'serving_size' => 100, 'calories' => 173, 'protein_g' => 16.0, 'carbs_g' => 21.0, 'fat_g' => 2.4, 'fiber_g' => 5.0, 'sodium_mg' => 1],
            ['name' => '병아리콩', 'name_en' => 'Chickpeas', 'category' => '단백질', 'serving_size' => 100, 'calories' => 364, 'protein_g' => 19.0, 'carbs_g' => 61.0, 'fat_g' => 6.0, 'fiber_g' => 17.0, 'sodium_mg' => 24],
            ['name' => '렌틸콩', 'name_en' => 'Lentils', 'category' => '단백질', 'serving_size' => 100, 'calories' => 116, 'protein_g' => 9.0, 'carbs_g' => 20.0, 'fat_g' => 0.4, 'fiber_g' => 8.0, 'sodium_mg' => 2],
            ['name' => '닭다리살', 'name_en' => 'Chicken Thigh', 'category' => '단백질', 'serving_size' => 100, 'calories' => 209, 'protein_g' => 26.0, 'carbs_g' => 0, 'fat_g' => 11.0, 'fiber_g' => 0, 'sodium_mg' => 95],
            ['name' => '삼겹살', 'name_en' => 'Pork Belly', 'category' => '단백질', 'serving_size' => 100, 'calories' => 518, 'protein_g' => 9.3, 'carbs_g' => 0, 'fat_g' => 53.0, 'fiber_g' => 0, 'sodium_mg' => 49],
            ['name' => '베이컨', 'name_en' => 'Bacon', 'category' => '단백질', 'serving_size' => 30, 'calories' => 165, 'protein_g' => 11.0, 'carbs_g' => 1.4, 'fat_g' => 13.0, 'fiber_g' => 0, 'sodium_mg' => 580],
            ['name' => '소시지', 'name_en' => 'Sausage', 'category' => '단백질', 'serving_size' => 50, 'calories' => 135, 'protein_g' => 6.0, 'carbs_g' => 2.0, 'fat_g' => 11.5, 'fiber_g' => 0, 'sodium_mg' => 480],
            ['name' => '햄', 'name_en' => 'Ham', 'category' => '단백질', 'serving_size' => 50, 'calories' => 72, 'protein_g' => 10.0, 'carbs_g' => 2.5, 'fat_g' => 2.5, 'fiber_g' => 0, 'sodium_mg' => 590],
            ['name' => '청국장', 'name_en' => 'Cheonggukjang', 'category' => '단백질', 'serving_size' => 100, 'calories' => 141, 'protein_g' => 14.0, 'carbs_g' => 9.5, 'fat_g' => 5.7, 'fiber_g' => 5.5, 'sodium_mg' => 600],
            ['name' => '된장', 'name_en' => 'Doenjang', 'category' => '단백질', 'serving_size' => 20, 'calories' => 26, 'protein_g' => 2.8, 'carbs_g' => 3.2, 'fat_g' => 0.8, 'fiber_g' => 0.9, 'sodium_mg' => 900],
            ['name' => '낫또', 'name_en' => 'Natto', 'category' => '단백질', 'serving_size' => 50, 'calories' => 100, 'protein_g' => 8.3, 'carbs_g' => 6.1, 'fat_g' => 5.0, 'fiber_g' => 2.7, 'sodium_mg' => 1],
            ['name' => '갈치', 'name_en' => 'Cutlassfish', 'category' => '단백질', 'serving_size' => 100, 'calories' => 127, 'protein_g' => 20.0, 'carbs_g' => 0, 'fat_g' => 5.0, 'fiber_g' => 0, 'sodium_mg' => 100],
            ['name' => '광어', 'name_en' => 'Flatfish', 'category' => '단백질', 'serving_size' => 100, 'calories' => 100, 'protein_g' => 20.0, 'carbs_g' => 0, 'fat_g' => 1.9, 'fiber_g' => 0, 'sodium_mg' => 78],
            ['name' => '명란젓', 'name_en' => 'Spicy Cod Roe', 'category' => '단백질', 'serving_size' => 30, 'calories' => 44, 'protein_g' => 7.0, 'carbs_g' => 1.5, 'fat_g' => 1.2, 'fiber_g' => 0, 'sodium_mg' => 720],
            ['name' => '어묵', 'name_en' => 'Fish Cake', 'category' => '단백질', 'serving_size' => 100, 'calories' => 135, 'protein_g' => 12.0, 'carbs_g' => 12.0, 'fat_g' => 4.0, 'fiber_g' => 0.5, 'sodium_mg' => 850],

            // 채소 (20개) - continuing...
            ['name' => '브로콜리', 'name_en' => 'Broccoli', 'category' => '채소', 'serving_size' => 100, 'calories' => 34, 'protein_g' => 2.8, 'carbs_g' => 7.0, 'fat_g' => 0.4, 'fiber_g' => 2.6, 'sodium_mg' => 33],
            ['name' => '시금치', 'name_en' => 'Spinach', 'category' => '채소', 'serving_size' => 100, 'calories' => 23, 'protein_g' => 2.9, 'carbs_g' => 3.6, 'fat_g' => 0.4, 'fiber_g' => 2.2, 'sodium_mg' => 79],
            ['name' => '양배추', 'name_en' => 'Cabbage', 'category' => '채소', 'serving_size' => 100, 'calories' => 25, 'protein_g' => 1.3, 'carbs_g' => 5.8, 'fat_g' => 0.1, 'fiber_g' => 2.5, 'sodium_mg' => 18],
            ['name' => '당근', 'name_en' => 'Carrot', 'category' => '채소', 'serving_size' => 100, 'calories' => 41, 'protein_g' => 0.9, 'carbs_g' => 9.6, 'fat_g' => 0.2, 'fiber_g' => 2.8, 'sodium_mg' => 69],
            ['name' => '오이', 'name_en' => 'Cucumber', 'category' => '채소', 'serving_size' => 100, 'calories' => 15, 'protein_g' => 0.7, 'carbs_g' => 3.6, 'fat_g' => 0.1, 'fiber_g' => 0.5, 'sodium_mg' => 2],
            ['name' => '토마토', 'name_en' => 'Tomato', 'category' => '채소', 'serving_size' => 150, 'calories' => 27, 'protein_g' => 1.3, 'carbs_g' => 5.8, 'fat_g' => 0.3, 'fiber_g' => 1.8, 'sodium_mg' => 8],
            ['name' => '양파', 'name_en' => 'Onion', 'category' => '채소', 'serving_size' => 100, 'calories' => 40, 'protein_g' => 1.1, 'carbs_g' => 9.3, 'fat_g' => 0.1, 'fiber_g' => 1.7, 'sodium_mg' => 4],
            ['name' => '피망', 'name_en' => 'Bell Pepper', 'category' => '채소', 'serving_size' => 100, 'calories' => 26, 'protein_g' => 1.0, 'carbs_g' => 6.0, 'fat_g' => 0.3, 'fiber_g' => 2.1, 'sodium_mg' => 4],
            ['name' => '가지', 'name_en' => 'Eggplant', 'category' => '채소', 'serving_size' => 100, 'calories' => 25, 'protein_g' => 1.0, 'carbs_g' => 5.9, 'fat_g' => 0.2, 'fiber_g' => 3.0, 'sodium_mg' => 2],
            ['name' => '호박', 'name_en' => 'Zucchini', 'category' => '채소', 'serving_size' => 100, 'calories' => 17, 'protein_g' => 1.2, 'carbs_g' => 3.1, 'fat_g' => 0.3, 'fiber_g' => 1.0, 'sodium_mg' => 8],
            ['name' => '상추', 'name_en' => 'Lettuce', 'category' => '채소', 'serving_size' => 100, 'calories' => 15, 'protein_g' => 1.4, 'carbs_g' => 2.9, 'fat_g' => 0.2, 'fiber_g' => 1.3, 'sodium_mg' => 28],
            ['name' => '배추', 'name_en' => 'Napa Cabbage', 'category' => '채소', 'serving_size' => 100, 'calories' => 13, 'protein_g' => 1.2, 'carbs_g' => 2.2, 'fat_g' => 0.2, 'fiber_g' => 1.2, 'sodium_mg' => 9],
            ['name' => '무', 'name_en' => 'Radish', 'category' => '채소', 'serving_size' => 100, 'calories' => 18, 'protein_g' => 0.7, 'carbs_g' => 4.1, 'fat_g' => 0.1, 'fiber_g' => 1.6, 'sodium_mg' => 39],
            ['name' => '콩나물', 'name_en' => 'Bean Sprouts', 'category' => '채소', 'serving_size' => 100, 'calories' => 30, 'protein_g' => 3.0, 'carbs_g' => 5.9, 'fat_g' => 0.1, 'fiber_g' => 2.6, 'sodium_mg' => 6],
            ['name' => '숙주나물', 'name_en' => 'Mung Bean Sprouts', 'category' => '채소', 'serving_size' => 100, 'calories' => 31, 'protein_g' => 3.0, 'carbs_g' => 5.9, 'fat_g' => 0.2, 'fiber_g' => 1.8, 'sodium_mg' => 6],
            ['name' => '미역', 'name_en' => 'Seaweed', 'category' => '채소', 'serving_size' => 100, 'calories' => 45, 'protein_g' => 1.7, 'carbs_g' => 9.1, 'fat_g' => 0.6, 'fiber_g' => 0.5, 'sodium_mg' => 872],
            ['name' => '김', 'name_en' => 'Laver', 'category' => '채소', 'serving_size' => 3, 'calories' => 5, 'protein_g' => 1.2, 'carbs_g' => 0.2, 'fat_g' => 0.1, 'fiber_g' => 0.3, 'sodium_mg' => 20],
            ['name' => '깻잎', 'name_en' => 'Perilla Leaves', 'category' => '채소', 'serving_size' => 10, 'calories' => 4, 'protein_g' => 0.4, 'carbs_g' => 0.7, 'fat_g' => 0.1, 'fiber_g' => 0.4, 'sodium_mg' => 1],
            ['name' => '부추', 'name_en' => 'Chives', 'category' => '채소', 'serving_size' => 50, 'calories' => 16, 'protein_g' => 1.8, 'carbs_g' => 2.2, 'fat_g' => 0.3, 'fiber_g' => 1.4, 'sodium_mg' => 2],
            ['name' => '청경채', 'name_en' => 'Bok Choy', 'category' => '채소', 'serving_size' => 100, 'calories' => 13, 'protein_g' => 1.5, 'carbs_g' => 2.2, 'fat_g' => 0.2, 'fiber_g' => 1.0, 'sodium_mg' => 65],

            // 과일 (15개)
            ['name' => '사과', 'name_en' => 'Apple', 'category' => '과일', 'serving_size' => 200, 'calories' => 104, 'protein_g' => 0.5, 'carbs_g' => 27.6, 'fat_g' => 0.3, 'fiber_g' => 4.8, 'sodium_mg' => 2],
            ['name' => '바나나', 'name_en' => 'Banana', 'category' => '과일', 'serving_size' => 120, 'calories' => 105, 'protein_g' => 1.3, 'carbs_g' => 27.0, 'fat_g' => 0.4, 'fiber_g' => 3.1, 'sodium_mg' => 1],
            ['name' => '딸기', 'name_en' => 'Strawberry', 'category' => '과일', 'serving_size' => 150, 'calories' => 48, 'protein_g' => 1.0, 'carbs_g' => 11.7, 'fat_g' => 0.5, 'fiber_g' => 3.0, 'sodium_mg' => 2],
            ['name' => '포도', 'name_en' => 'Grape', 'category' => '과일', 'serving_size' => 150, 'calories' => 104, 'protein_g' => 1.1, 'carbs_g' => 27.3, 'fat_g' => 0.2, 'fiber_g' => 1.4, 'sodium_mg' => 3],
            ['name' => '수박', 'name_en' => 'Watermelon', 'category' => '과일', 'serving_size' => 200, 'calories' => 60, 'protein_g' => 1.2, 'carbs_g' => 15.2, 'fat_g' => 0.3, 'fiber_g' => 0.8, 'sodium_mg' => 2],
            ['name' => '오렌지', 'name_en' => 'Orange', 'category' => '과일', 'serving_size' => 150, 'calories' => 70, 'protein_g' => 1.4, 'carbs_g' => 17.7, 'fat_g' => 0.2, 'fiber_g' => 3.6, 'sodium_mg' => 0],
            ['name' => '키위', 'name_en' => 'Kiwi', 'category' => '과일', 'serving_size' => 80, 'calories' => 49, 'protein_g' => 0.9, 'carbs_g' => 11.7, 'fat_g' => 0.4, 'fiber_g' => 2.4, 'sodium_mg' => 2],
            ['name' => '블루베리', 'name_en' => 'Blueberry', 'category' => '과일', 'serving_size' => 100, 'calories' => 57, 'protein_g' => 0.7, 'carbs_g' => 14.5, 'fat_g' => 0.3, 'fiber_g' => 2.4, 'sodium_mg' => 1],
            ['name' => '망고', 'name_en' => 'Mango', 'category' => '과일', 'serving_size' => 150, 'calories' => 90, 'protein_g' => 1.4, 'carbs_g' => 22.5, 'fat_g' => 0.6, 'fiber_g' => 2.6, 'sodium_mg' => 2],
            ['name' => '복숭아', 'name_en' => 'Peach', 'category' => '과일', 'serving_size' => 150, 'calories' => 59, 'protein_g' => 1.4, 'carbs_g' => 14.3, 'fat_g' => 0.4, 'fiber_g' => 2.3, 'sodium_mg' => 0],
            ['name' => '배', 'name_en' => 'Pear', 'category' => '과일', 'serving_size' => 180, 'calories' => 103, 'protein_g' => 0.6, 'carbs_g' => 27.5, 'fat_g' => 0.3, 'fiber_g' => 5.5, 'sodium_mg' => 2],
            ['name' => '자두', 'name_en' => 'Plum', 'category' => '과일', 'serving_size' => 100, 'calories' => 46, 'protein_g' => 0.7, 'carbs_g' => 11.4, 'fat_g' => 0.3, 'fiber_g' => 1.4, 'sodium_mg' => 0],
            ['name' => '체리', 'name_en' => 'Cherry', 'category' => '과일', 'serving_size' => 100, 'calories' => 63, 'protein_g' => 1.1, 'carbs_g' => 16.0, 'fat_g' => 0.2, 'fiber_g' => 2.1, 'sodium_mg' => 0],
            ['name' => '파인애플', 'name_en' => 'Pineapple', 'category' => '과일', 'serving_size' => 150, 'calories' => 75, 'protein_g' => 0.8, 'carbs_g' => 19.7, 'fat_g' => 0.2, 'fiber_g' => 2.1, 'sodium_mg' => 2],
            ['name' => '감', 'name_en' => 'Persimmon', 'category' => '과일', 'serving_size' => 150, 'calories' => 105, 'protein_g' => 0.9, 'carbs_g' => 27.9, 'fat_g' => 0.3, 'fiber_g' => 5.6, 'sodium_mg' => 2],

            // 유제품 (10개)
            ['name' => '우유', 'name_en' => 'Milk', 'category' => '유제품', 'serving_size' => 200, 'calories' => 122, 'protein_g' => 6.6, 'carbs_g' => 9.4, 'fat_g' => 6.4, 'fiber_g' => 0, 'sodium_mg' => 98],
            ['name' => '저지방우유', 'name_en' => 'Low-fat Milk', 'category' => '유제품', 'serving_size' => 200, 'calories' => 86, 'protein_g' => 6.8, 'carbs_g' => 9.8, 'fat_g' => 2.4, 'fiber_g' => 0, 'sodium_mg' => 102],
            ['name' => '무지방우유', 'name_en' => 'Skim Milk', 'category' => '유제품', 'serving_size' => 200, 'calories' => 68, 'protein_g' => 6.8, 'carbs_g' => 10.0, 'fat_g' => 0.4, 'fiber_g' => 0, 'sodium_mg' => 106],
            ['name' => '요거트', 'name_en' => 'Yogurt', 'category' => '유제품', 'serving_size' => 100, 'calories' => 61, 'protein_g' => 3.5, 'carbs_g' => 4.7, 'fat_g' => 3.3, 'fiber_g' => 0, 'sodium_mg' => 46],
            ['name' => '그릭요거트', 'name_en' => 'Greek Yogurt', 'category' => '유제품', 'serving_size' => 100, 'calories' => 59, 'protein_g' => 10.0, 'carbs_g' => 3.6, 'fat_g' => 0.4, 'fiber_g' => 0, 'sodium_mg' => 36],
            ['name' => '치즈', 'name_en' => 'Cheese', 'category' => '유제품', 'serving_size' => 30, 'calories' => 120, 'protein_g' => 7.0, 'carbs_g' => 0.4, 'fat_g' => 10.0, 'fiber_g' => 0, 'sodium_mg' => 180],
            ['name' => '모짜렐라치즈', 'name_en' => 'Mozzarella', 'category' => '유제품', 'serving_size' => 30, 'calories' => 85, 'protein_g' => 6.3, 'carbs_g' => 0.6, 'fat_g' => 6.3, 'fiber_g' => 0, 'sodium_mg' => 178],
            ['name' => '크림치즈', 'name_en' => 'Cream Cheese', 'category' => '유제품', 'serving_size' => 30, 'calories' => 99, 'protein_g' => 1.8, 'carbs_g' => 1.2, 'fat_g' => 9.9, 'fiber_g' => 0, 'sodium_mg' => 105],
            ['name' => '두유', 'name_en' => 'Soy Milk', 'category' => '유제품', 'serving_size' => 200, 'calories' => 80, 'protein_g' => 7.0, 'carbs_g' => 4.0, 'fat_g' => 4.0, 'fiber_g' => 2.0, 'sodium_mg' => 90],
            ['name' => '아몬드우유', 'name_en' => 'Almond Milk', 'category' => '유제품', 'serving_size' => 200, 'calories' => 30, 'protein_g' => 1.0, 'carbs_g' => 1.0, 'fat_g' => 2.5, 'fiber_g' => 0.5, 'sodium_mg' => 150],

            // 견과류 (10개)
            ['name' => '아몬드', 'name_en' => 'Almonds', 'category' => '견과류', 'serving_size' => 30, 'calories' => 170, 'protein_g' => 6.0, 'carbs_g' => 6.0, 'fat_g' => 15.0, 'fiber_g' => 3.5, 'sodium_mg' => 0],
            ['name' => '호두', 'name_en' => 'Walnuts', 'category' => '견과류', 'serving_size' => 30, 'calories' => 196, 'protein_g' => 4.6, 'carbs_g' => 4.1, 'fat_g' => 19.6, 'fiber_g' => 2.0, 'sodium_mg' => 1],
            ['name' => '땅콩', 'name_en' => 'Peanuts', 'category' => '견과류', 'serving_size' => 30, 'calories' => 170, 'protein_g' => 7.0, 'carbs_g' => 5.0, 'fat_g' => 14.0, 'fiber_g' => 2.5, 'sodium_mg' => 5],
            ['name' => '캐슈넛', 'name_en' => 'Cashews', 'category' => '견과류', 'serving_size' => 30, 'calories' => 157, 'protein_g' => 5.2, 'carbs_g' => 8.6, 'fat_g' => 12.4, 'fiber_g' => 0.9, 'sodium_mg' => 3],
            ['name' => '피스타치오', 'name_en' => 'Pistachios', 'category' => '견과류', 'serving_size' => 30, 'calories' => 159, 'protein_g' => 5.7, 'carbs_g' => 7.7, 'fat_g' => 12.9, 'fiber_g' => 3.0, 'sodium_mg' => 0],
            ['name' => '해바라기씨', 'name_en' => 'Sunflower Seeds', 'category' => '견과류', 'serving_size' => 30, 'calories' => 165, 'protein_g' => 5.8, 'carbs_g' => 5.6, 'fat_g' => 14.3, 'fiber_g' => 2.4, 'sodium_mg' => 3],
            ['name' => '호박씨', 'name_en' => 'Pumpkin Seeds', 'category' => '견과류', 'serving_size' => 30, 'calories' => 151, 'protein_g' => 7.0, 'carbs_g' => 5.0, 'fat_g' => 13.0, 'fiber_g' => 1.1, 'sodium_mg' => 5],
            ['name' => '마카다미아', 'name_en' => 'Macadamia', 'category' => '견과류', 'serving_size' => 30, 'calories' => 204, 'protein_g' => 2.2, 'carbs_g' => 3.9, 'fat_g' => 21.5, 'fiber_g' => 2.4, 'sodium_mg' => 1],
            ['name' => '피칸', 'name_en' => 'Pecans', 'category' => '견과류', 'serving_size' => 30, 'calories' => 196, 'protein_g' => 2.6, 'carbs_g' => 3.9, 'fat_g' => 20.4, 'fiber_g' => 2.7, 'sodium_mg' => 0],
            ['name' => '잣', 'name_en' => 'Pine Nuts', 'category' => '견과류', 'serving_size' => 30, 'calories' => 191, 'protein_g' => 3.9, 'carbs_g' => 3.7, 'fat_g' => 19.4, 'fiber_g' => 1.0, 'sodium_mg' => 1],
        ];

        foreach ($foods as $food) {
            Food::create($food);
        }
    }
}
