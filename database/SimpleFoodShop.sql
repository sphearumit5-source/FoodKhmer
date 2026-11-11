-- 6. Sample Products (Cambodian Riel - KHR)

INSERT INTO products (
    category_id,
    name_kh,
    name_en,
    description_kh,
    description_en,
    price,
    image,
    is_available,
    is_featured
) VALUES

(1, 'បាយឆាសាច់ជ្រូកពងទា',
 'Pork Fried Rice with Egg',
 'បាយឆាឈ្ងុយឆ្ងាញ់ ជាមួយសាច់ជ្រូកស្រស់ និងពងទាចៀនក្រៀម',
 'Savory fried rice with tender pork and a crispy fried egg',
 10000, 'pork-fried-rice.jpg', TRUE, TRUE),

(1, 'គុយទាវសាច់គោពិសេស',
 'Special Beef Noodle Soup',
 'គុយទាវទឹកស៊ុបឆ្អឹងគោផ្អែមឈ្ងុយ សាច់គោផុយទន់',
 'Flavorful beef broth with soft rice noodles and tender beef slices',
 12000, 'beef-noodle.jpg', TRUE, TRUE),

(1, 'ឡុកឡាក់សាច់គោបាយស',
 'Beef Lok Lak with Rice',
 'សាច់គោផុយឆាទឹកជ្រលក់ពិសេស ញ៉ាំជាមួយទឹកម្រេចក្រូចឆ្មា',
 'Tender stir-fried beef cubes served with fresh salad, rice, and pepper lime dip',
 14000, 'beef-loklak.jpg', TRUE, FALSE),

(2, 'កាហ្វេទឹកដោះគោទឹកកក',
 'Iced Milk Coffee',
 'កាហ្វេគ្រាប់ឈ្ងុយឆ្ងាញ់ ទឹកដោះគោផ្អែមស្រទន់',
 'Rich brewed coffee mixed with sweetened condensed milk over ice',
 5000, 'iced-coffee.jpg', TRUE, TRUE),

(2, 'តែបៃតងទឹកដោះគោ',
 'Iced Green Tea Latte',
 'តែបៃតងដាំស្រស់ រសជាតិឈ្ងុយផ្អែមត្រជាក់ចិត្ត',
 'Freshly brewed green tea with silky milk and ice',
 6000, 'green-tea.jpg', TRUE, FALSE),

(2, 'ទឹកក្រូចច្របាច់ស្រស់',
 'Fresh Orange Juice',
 'ទឹកក្រូចស្រស់ធម្មជាតិ ១០០% មានវីតាមីន C ខ្ពស់',
 '100% freshly squeezed sweet natural orange juice',
 6000, 'orange-juice.jpg', TRUE, FALSE),

(3, 'ប្រហិតចៀនចម្រុះ',
 'Mixed Fried Meatballs',
 'ប្រហិតសាច់គោ ត្រី បង្គា ចៀនស្រួយជាមួយទឹកជ្រលក់ពិសេស',
 'Crispy assorted fried meatballs served with sweet chili dip and veggies',
 8000, 'mixed-meatballs.jpg', TRUE, FALSE),

(3, 'ស្លាបមាន់បំពងទឹកឃ្មុំ',
 'Crispy Honey Chicken Wings',
 'ស្លាបមាន់បំពងស្រួយស្រោបទឹកឃ្មុំឈ្ងុយផ្អែម',
 'Golden fried chicken wings coated in sweet savory honey glaze',
 11000, 'honey-wings.jpg', FALSE, FALSE),

(4, 'ស្រាបៀរអង្គរ (កំប៉ុង)',
 'Angkor Beer (Can)',
 'ស្រាបៀរជាតិពិតៗ ត្រជាក់ស្រួលបំពង់ក (៣៣០មល)',
 'Cambodia iconic lager, served ice cold (330ml)',
 4000, 'angkor-beer.jpg', TRUE, FALSE),

(4, 'ស្រាបៀរហនុមាន (កំប៉ុង)',
 'Hanuman Beer (Can)',
 'ស្រាបៀរប្រណិត រសជាតិស្រទន់ (៣៣០មល)',
 'Premium smooth craft lager (330ml)',
 5000, 'hanuman-beer.jpg', TRUE, FALSE);