-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 16, 2026 at 09:12 PM
-- Server version: 8.0.40
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inktale_simple_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `username`, `password`, `full_name`) VALUES
(1, 'admin', '$2y$10$QgtJ1JERcucJ4cIX3DGBl.BWF/o/VcDD1yWA0GW6TEX.uEQwd5ebq', 'Demo Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int NOT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `picture` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '100',
  `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `language` enum('Arabic','English') COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `author_bio` text COLLATE utf8mb4_unicode_ci,
  `author_picture` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `json_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `picture`, `price`, `stock`, `category`, `language`, `author_name`, `author_bio`, `author_picture`, `json_path`, `created_at`) VALUES
(1, 'The Hobbit', 'Bilbo Baggins, a quiet hobbit who prefers a peaceful life, is unexpectedly drawn into an epic adventure when the wizard Gandalf and a group of dwarves recruit him to help reclaim their stolen treasure from the dragon Smaug.', 'bookIMG/the_hobbit.jpg', 45.00, 100, 'Fantasy', 'English', 'J.R.R. Tolkien', 'J.R.R. Tolkien was an English writer and professor best known for creating the fantasy world of Middle-earth. His works, including The Hobbit and The Lord of the Rings, helped shape modern fantasy literature.', 'images/authors/j_r_r_tolkien.jpg', 'text/english/The_Hobbit.json', '2026-04-16 21:50:21'),
(2, 'Harry Potter and the Sorcerer’s Stone', 'Harry Potter, an orphan raised by unkind relatives, discovers on his eleventh birthday that he is actually a wizard. He begins studying magic at Hogwarts School and uncovers the truth about his past.', 'bookIMG/harry_potter_and_he_sorcerer_s_stone.jpg', 45.00, 100, 'Fantasy', 'English', 'J.K. Rowling', 'J.K. Rowling is a British author famous for the Harry Potter series, one of the best-selling book series in history.', 'images/authors/j_k_rowling.jpg', 'text/english/Harry_Potter_and_he_Sorcerer’s_Stone.json', '2026-04-16 21:50:21'),
(3, 'The Hunger Games', 'Katniss Everdeen volunteers to compete in a deadly televised survival game in a dystopian society, where only one tribute is supposed to survive.', 'bookIMG/the_hunger_games.jpg', 45.00, 100, 'Fantasy', 'English', 'Suzanne Collins', 'Suzanne Collins is an American writer known for young adult dystopian fiction, especially The Hunger Games trilogy.', 'images/authors/suzanne_collins.jpg', 'text/english/The_Hunger_Games.json', '2026-04-16 21:50:21'),
(4, 'Alice’s Adventures in Wonderland', 'Alice follows a white rabbit into a magical world full of strange creatures, unusual adventures, and unforgettable characters.', 'bookIMG/alice_s_adventures_in_wonderland.jpg', 45.00, 100, 'Fantasy', 'English', 'Lewis Carroll', 'Lewis Carroll was an English writer and mathematician famous for his imaginative children’s stories and playful use of language.', 'images/authors/lewis_carroll.jpg', 'text/english/Alice’s_Adventures_in_Wonderland.json', '2026-04-16 21:51:40'),
(5, 'A Game of Thrones', 'Noble families compete for power over the Iron Throne while danger rises in the North and across the kingdom.', 'bookIMG/a_game_of_thrones.jpg', 45.00, 100, 'Fantasy', 'English', 'George R.R. Martin', 'George R.R. Martin is an American novelist famous for the epic fantasy series A Song of Ice and Fire.', 'images/authors/george_r_r_martin.jpg', 'text/english/A_Game_of_Thrones.json', '2026-04-16 21:51:40'),
(6, 'قواعد جارتين', 'رواية خيالية تدور في عالم تحكمه قوانين صارمة وغريبة تتحكم في حياة الناس، وتكشف أسرارًا وصراعات تهدد استقرار هذا العالم.', 'bookIMG/qwaad_jartyn.jpg', 40.00, 100, 'Fantasy', 'Arabic', 'عمرو عبد الحميد', 'عمرو عبد الحميد كاتب وروائي مصري اشتهر بالروايات الخيالية مثل أرض زيكولا وقواعد جارتين.', 'images/authors/amrw_abd_alhmyd.jpg', 'text/arabic/قواعد_جارتين.json', '2026-04-16 21:51:40'),
(7, 'الفيل الأزرق', 'تدور القصة حول طبيب نفسي يكتشف أحداثًا غامضة وخارقة للطبيعة مرتبطة بأحد مرضاه، في رحلة مليئة بالغموض والإثارة.', 'bookIMG/alfyl_alazrq.jpg', 40.00, 100, 'Fantasy', 'Arabic', 'أحمد مراد', 'أحمد مراد كاتب وروائي وسيناريست مصري، اشتهر بروايات الإثارة والغموض التي تحولت بعضُها إلى أفلام سينمائية.', 'images/authors/ahmd_mrad.jpg', 'text/arabic/الفيل_الأزرق.json', '2026-04-16 21:52:58'),
(8, 'خوف', 'رواية فانتازية تتحدث عن عالم خفي مليء بالسحر والظواهر الغامضة، وتدور أحداثها في أجواء مشوقة ومليئة بالأسرار.', 'bookIMG/khwf.jpg', 40.00, 100, 'Fantasy', 'Arabic', 'أسامة المسلم', 'أسامة المسلم كاتب سعودي معروف في أدب الفانتازيا والروايات الغامضة، وله سلسلة روايات مشهورة في العالم العربي.', 'images/authors/asama_almslm.jpg', 'text/arabic/خوف.json', '2026-04-16 21:52:58'),
(9, 'ألف ليلة و ليلة', 'مجموعة قصص خيالية ترويها شهرزاد للملك شهريار، وتضم مغامرات وسحرًا وشخصيات أسطورية من التراث العربي.', 'bookIMG/alf_lyla_w_lyla.jpg', 40.00, 100, 'Fantasy', 'Arabic', 'مجهول (من التراث العربي)', 'هذا الكتاب من التراث الشعبي العربي، وقد جُمعت قصصه عبر القرون من عدة ثقافات مثل العربية والفارسية والهندية.', '', 'text/arabic/ألف_ليلة_و_ليلة.json', '2026-04-16 21:52:58'),
(10, 'The Time Machine', 'A scientist builds a machine that allows him to travel through time and discovers the future of humanity.', 'bookIMG/the_time_machine.jpg', 45.00, 100, 'Fantasy', 'English', 'H.G. Wells', 'H.G. Wells was a British writer known as one of the pioneers of science fiction. His works influenced modern fantasy and science fiction literature.', 'images/authors/h_g_wells.jpg', 'text/english/The_Time_Machine.json', '2026-04-16 21:54:06'),
(11, 'البؤساء', 'رواية فرنسية شهيرة تدور حول الفقر والعدالة والرحمة، وتتابع حياة جان فالجان وصراعه مع المجتمع والقانون، وتعرض أحوال فرنسا في القرن التاسع عشر.', 'bookIMG/albosa.jpg', 40.00, 100, 'Drama', 'Arabic', 'فيكتور هوغو', 'فيكتور هوغو أديب وشاعر وروائي فرنسي كبير، ويُعد من أهم أعلام الأدب الفرنسي. اشتهر بأعمال إنسانية واجتماعية بارزة مثل البؤساء وأحدب نوتردام.', 'images/authors/fyktwr_hwghw.jpg', 'text/arabic/البؤساء.json', '2026-04-16 21:54:06'),
(12, 'Heidi', 'A warm and touching story about the young girl Heidi, whose innocence and kindness transform the lives of those around her.', 'bookIMG/heidi.jpg', 45.00, 100, 'Drama', 'English', 'Johanna Spyri', 'Johanna Spyri was a Swiss author best known for children’s literature, and Heidi remains her most famous and beloved work.', 'images/authors/johanna_spyri.jpg', 'text/english/Heidi.json', '2026-04-16 21:54:06'),
(13, 'Anne of Green Gables', 'A novel about Anne Shirley, an imaginative orphan girl who begins a new life and leaves a deep impact on everyone she meets.', 'bookIMG/anne_of_green_gables.jpg', 45.00, 100, 'Drama', 'English', 'L. M. Montgomery', 'L. M. Montgomery was a Canadian novelist known for writing stories that blend childhood, imagination, and human emotion.', 'images/authors/l_m_montgomery.jpg', 'text/english/Anne_of_Green_Gables.json', '2026-04-16 21:54:24'),
(14, 'جين إير', 'رواية كلاسيكية تتناول حياة جين إير منذ طفولتها الصعبة حتى نضجها، وتجمع بين الرومانسية والاستقلال الشخصي والصراع الأخلاقي.', 'bookIMG/jyn_iyr.jpg', 40.00, 100, 'Drama', 'Arabic', 'شارلوت برونتي', 'شارلوت برونتي روائية إنجليزية بارزة من القرن التاسع عشر، عُرفت بأسلوبها العاطفي العميق واهتمامها بقضايا المرأة والكرامة الإنسانية.', 'images/authors/sharlwt_brwnty.jpg', 'text/arabic/جين_إير.json', '2026-04-16 21:54:24'),
(15, 'Wonder', 'A contemporary novel about Auggie, a boy born with a facial difference, and his journey through friendship, kindness, and acceptance.', 'bookIMG/wonder.jpg', 45.00, 100, 'Drama', 'English', 'R. J. Palacio', 'R. J. Palacio is an American author known for Wonder, a widely loved novel praised for its powerful human message.', 'images/authors/r_j_palacio.jpg', 'text/english/wonder.json', '2026-04-16 21:54:24'),
(16, 'نساء صغيرات', 'رواية تدور حول حياة أربع أخوات وعلاقتهن بالأسرة والأحلام والمسؤولية، وتُعد من أشهر الروايات الكلاسيكية الموجهة لليافعين.', 'bookIMG/nsa_sghyrat.jpg', 40.00, 100, 'Drama', 'Arabic', 'لويزا ماي ألكوت', 'لويزا ماي ألكوت روائية أمريكية اشتهرت بأدب الفتيات والأسرة، وكانت كتاباتها قريبة من الحياة اليومية والقيم الإنسانية.', 'images/authors/lwyza_may_alkwt.jpg', 'text/arabic/نساء_صغيرات.json', '2026-04-16 21:54:57'),
(17, 'الشيخ والبحر', 'رواية قصيرة تحكي قصة صياد عجوز يخوض معركة طويلة مع سمكة ضخمة في البحر، وتبرز الصبر والكبرياء ومعنى الكفاح.', 'bookIMG/alshykh_w_albhr.jpg', 40.00, 100, 'Drama', 'Arabic', 'إرنست همنغواي', 'إرنست همنغواي كاتب أمريكي عالمي، عُرف بأسلوبه المكثف والبسيط، ويعد من أبرز كتاب القرن العشرين.', 'images/authors/irnst_hmnghway.jpg', 'text/arabic/الشيخ_و_البحر.json', '2026-04-16 21:54:57'),
(18, 'قصة مدينتين', 'رواية تاريخية تدور بين لندن وباريس في زمن الثورة الفرنسية، وتعرض التضحية والظلم والتحولات الاجتماعية الكبرى.', 'bookIMG/qsa_mdyntyn.jpg', 40.00, 100, 'Drama', 'Arabic', 'تشارلز ديكنز', 'تشارلز ديكنز روائي إنجليزي شهير، عُرف بقدرته على تصوير المجتمع ومشكلاته، وبشخصياته الأدبية الخالدة.', 'images/authors/tsharlz_dyknz.jpg', 'text/arabic/قصة_مدينتين.json', '2026-04-16 21:54:57'),
(19, 'The Little Prince', 'A poetic and philosophical story that appears simple on the surface, but explores love, friendship, loneliness, and life itself.', 'bookIMG/the_little_prince.jpg', 45.00, 100, 'Drama', 'English', 'Antoine de Saint-Exupéry', 'Antoine de Saint-Exupéry was a French writer and aviator whose works combined imagination with deep human reflection.', 'images/authors/antoine_de_saint-exupery.jpg', 'text/english/The_Little_Prince.json', '2026-04-16 21:55:21'),
(20, 'Jane Eyre', 'Jane Eyre, a poor orphan, becomes a governess at the mysterious Thornfield Hall, where she falls in love with the brooding Mr. Rochester while facing dark secrets and moral struggles.', 'bookIMG/jane_eyre.jpg', 45.00, 100, 'Romantic', 'English', 'Charlotte Brontë', 'Charlotte Brontë was an English novelist whose work transformed the portrayal of women in literature. Jane Eyre remains one of her most celebrated novels.', 'images/authors/charlotte_bronte.jpg', 'text/english/Jane_Eyre.json', '2026-04-16 21:55:21'),
(21, 'Sense and Sensibility', 'Two contrasting sisters — the calm and rational Elinor and the passionate, impulsive Marianne — search for love in a society that judges women by their wealth. Both face betrayal and heartbreak in their own way. A novel about the balance between head and heart.', 'bookIMG/sense_and_sensibility.jpg', 45.00, 100, 'Romantic', 'English', 'Jane Austen', 'Jane Austen was one of the greatest English novelists. Her works explored love, class, family, and the balance between reason and emotion.', 'images/authors/jane_austen.jpg', 'text/english/Sense_and_Sensibility.json', '2026-04-16 21:55:21'),
(22, 'Anna Karenina', 'Anna Karenina, wife of a prominent government official, falls for the charming officer Count Vronsky. She sacrifices her marriage, reputation, and even her son for love, but Russian society shows no mercy. An epic story about love, ambition, and the devastating price of following your heart.', 'bookIMG/anna_karenina.jpg', 45.00, 100, 'Romantic', 'English', 'Leo Tolstoy', 'Leo Tolstoy was a Russian novelist widely regarded as one of the greatest authors of all time. His most famous works include Anna Karenina and War and Peace.', 'images/authors/leo_tolstoy.jpg', 'text/english/Anna_Karenina.json', '2026-04-16 21:55:48'),
(23, 'The Great Gatsby', 'The mysterious millionaire Jay Gatsby throws lavish parties at his mansion with one goal: to win back Daisy, the woman he loves, who married someone else. A story about the American Dream, the impossibility of recapturing the past, and the illusions we build our lives upon.', 'bookIMG/the_great_gatsby.jpg', 45.00, 100, 'Romantic', 'English', 'F. Scott Fitzgerald', 'F. Scott Fitzgerald was an American novelist and one of the defining writers of the Jazz Age in the 1920s.', 'images/authors/scott_fitzgerald.jpg', 'text/english/The_Great_Gatsby.json', '2026-04-16 21:55:48'),
(24, 'Love Story', 'Oliver, from a wealthy family, and Jenny, from a poor one, meet at university and fall in love despite their differences. They marry against his family’s wishes and build a beautiful life together — until a piece of news shatters everything.', 'bookIMG/love_story.jpg', 45.00, 100, 'Romantic', 'English', 'Erich Segal', 'Erich Segal was an American author and professor. Love Story became one of the most famous romantic novels of the twentieth century.', 'images/authors/erich_segal.jpg', 'text/english/Love_Story.json', '2026-04-16 21:55:48'),
(25, 'The Notebook', 'Noah and Allie fall in love one summer in 1940, then are torn apart by social class and family disapproval. Years later, he finds her again — engaged to another man. A story about a love that defies time.', 'bookIMG/the_notebook.jpg', 45.00, 100, 'Romantic', 'English', 'Nicholas Sparks', 'Nicholas Sparks is an American novelist known for his bestselling romance novels, many of which were adapted into films.', 'images/authors/nicholas_sparks.jpg', 'text/english/The_Notebook.json', '2026-04-16 21:56:53'),
(26, 'Romeo and Juliet', 'Romeo from the Montague family and Juliet from the Capulet family — two households locked in a bitter feud in Verona. They meet at a party and fall instantly in love. They marry in secret, but fate and an ancient hatred drive their story toward a tragic end.', 'bookIMG/romeo_and_juliet.jpg', 40.00, 100, 'Romantic', 'Arabic', 'William Shakespeare', 'William Shakespeare was an English playwright and poet, widely regarded as the greatest writer in the English language.', 'images/authors/william_shakespeare.jpg', 'text/arabic/Romeo_and_Juliet.json', '2026-04-16 21:56:53'),
(27, 'ذاكرة الجسد', 'خالد، الرسام الجزائري الذي فقد ذراعه في حرب الاستقلال، يقع في حب حياة، ابنة قائده السابق. رواية تمزج الحب بالذاكرة والتاريخ والوطن.', 'bookIMG/thakra_aljsd.jpg', 40.00, 100, 'Romantic', 'Arabic', 'أحلام مستغانمي', 'أحلام مستغانمي روائية جزائرية تُعد من أكثر الكاتبات العربيات قراءة، واشتهرت بأسلوبها الشعري ورواياتها المؤثرة.', 'images/authors/ahlam_mosteghanemi.jpg', 'text/arabic/ذاكرة_الجسد.json', '2026-04-16 21:56:53'),
(28, 'الحب فوق هضبة الهرم', 'A tender and bittersweet love story set in modern Cairo. A middle-aged couple revisits their feelings after years of routine have dimmed their connection. Mahfouz explores what remains of love when youth fades — and whether it can be rekindled.', 'bookIMG/alhb_fwq_hdba_alhrm.jpg', 40.00, 100, 'Romantic', 'Arabic', 'نجيب محفوظ', 'نجيب محفوظ أديب مصري عالمي وأول عربي حصل على جائزة نوبل في الأدب عام 1988، وقدم أعمالًا تعكس المجتمع المصري بعمق.', 'images/authors/naguib_mahfouz.jpg', 'text/arabic/الحب_فوق_هضبة_الهرم.json', '2026-04-16 21:57:48'),
(29, 'السمان والخريف', 'A divorced former official unexpectedly falls in love with a young dancer، stirring feelings he thought were long dead. A quiet and moving story about loneliness، desire، and the search for human connection in the autumn of life.', 'bookIMG/alsman_walkhryf.jpg', 40.00, 100, 'Romantic', 'Arabic', 'نجيب محفوظ', 'نجيب محفوظ أديب مصري عالمي وأول عربي حاصل على جائزة نوبل، اشتهر برواياته الواقعية العميقة.', 'images/authors/naguib_mahfouz.jpg', 'text/arabic/السمان_والخريف.json', '2026-04-16 21:57:48'),
(30, 'The Jungle Book', 'The Jungle Book is a classic collection of stories set in the jungles of India. The most famous story follows Mowgli, a boy raised by wolves, who learns the laws of the jungle with the help of animals such as Baloo the bear, Bagheera the panther, and Kaa the python. The book combines adventure, friendship, and life lessons, making it a timeless story enjoyed by readers of all ages.', 'bookIMG/the_jungle_book.jpg', 45.00, 100, 'Action', 'English', 'Rudyard Kipling', 'Rudyard Kipling was a British author and poet known for his stories set in India and his contributions to children’s literature.', 'images/authors/rudyard_kipling.jpg', 'text/english/The_Jungle_Book.json', '2026-04-16 21:57:48'),
(31, 'Around the World in 80 Days', 'Around the World in Eighty Days is an adventure novel that follows the story of Phileas Fogg, a wealthy and precise English gentleman who makes a daring bet that he can travel around the world in just 80 days. Accompanied by his loyal servant Passepartout, Fogg travels across different countries and continents while facing many challenges and unexpected events. The novel is a thrilling story about adventure, determination, and the excitement of global travel.', 'bookIMG/around_the_world_in_80_days.jpg', 40.00, 100, 'Action', 'Arabic', 'Jules Verne', 'Jules Verne was a French writer widely regarded as one of the fathers of science fiction. His novels combined scientific ideas with adventure and imagination.', 'images/authors/jules_verne.jpg', 'text/arabic/Around_the_World_in_80_Days.json', '2026-04-16 21:58:40'),
(32, '20,000 Leagues Under the Sea', 'Twenty Thousand Leagues Under the Sea is a classic science fiction adventure about Professor Pierre Aronnax, his servant Conseil, and a harpooner named Ned Land, who are captured by the mysterious Captain Nemo aboard the advanced submarine Nautilus. Together they travel through the deep oceans of the world, discovering incredible sea creatures, underwater landscapes, and hidden wonders. The novel explores themes of exploration, science, and the mysteries of the ocean.', 'bookIMG/20_000_leagues_under_the_sea.jpg', 45.00, 100, 'Action', 'English', 'Jules Verne', 'Jules Verne was a French writer widely regarded as one of the fathers of science fiction. His novels combined scientific ideas with adventure and imagination.', 'images/authors/jules_verne.jpg', 'text/english/20,000_Leagues_Under_the_Sea.json', '2026-04-16 21:58:40'),
(33, 'The Call of the Wild', 'The Call of the Wild is an adventure novel that tells the story of Buck, a domesticated dog who is taken from his comfortable home in California and forced to work as a sled dog in the harsh wilderness of Alaska during the Klondike Gold Rush. As Buck adapts to the wild environment, he discovers his natural instincts and inner strength. The novel explores themes of survival, freedom, and the powerful pull of nature.', 'bookIMG/the_call_of_the_wild.jpg', 40.00, 100, 'Action', 'Arabic', 'Jack London', 'Jack London was an American writer known for adventure novels inspired by survival, wilderness, and his experiences during the Klondike Gold Rush.', 'images/authors/jack_london.jpg', 'text/arabic/The_Call_of_the_Wild.json', '2026-04-16 21:58:40'),
(34, 'Treasure Island', 'Treasure Island is a classic adventure novel that tells the story of a young boy named Jim Hawkins who discovers a mysterious treasure map. Along with a group of sailors, he sets out on a dangerous journey to find the hidden treasure on a distant island. During the voyage, Jim encounters pirates, betrayal, and the famous pirate Long John Silver. The novel is filled with excitement, mystery, and themes of courage and adventure.', 'bookIMG/treasure_island.jpg', 40.00, 100, 'Action', 'Arabic', 'Robert Louis Stevenson', 'Robert Louis Stevenson was a Scottish novelist, poet, and travel writer best known for adventure fiction and imaginative storytelling.', 'images/authors/robert_louis_stevenson.jpg', 'text/arabic/Treasure_Island.json', '2026-04-16 21:59:39'),
(35, 'The Count of Monte Cristo', 'The Count of Monte Cristo is a classic adventure and revenge novel that follows Edmond Dantès, a young sailor who is falsely accused of a crime and imprisoned for many years. After escaping from prison and discovering a hidden treasure, he returns with a new identity as the Count of Monte Cristo. Using his wealth and intelligence, he carefully plans his revenge against those who betrayed him. The novel explores themes of justice, betrayal, revenge, and redemption.', 'bookIMG/the_count_of_monte_cristo.jpg', 45.00, 100, 'Action', 'English', 'Alexandre Dumas', 'Alexandre Dumas was a famous French writer known for historical adventure novels such as The Count of Monte Cristo and The Three Musketeers.', 'images/authors/alexandre_dumas.jpg', 'text/english/The_Count_of_Monte_Cristo.json', '2026-04-16 21:59:39'),
(36, 'The Three Musketeers', 'The Three Musketeers is a historical adventure novel that follows the young and ambitious d’Artagnan who travels to Paris to join the Musketeers of the Guard. There he befriends Athos, Porthos, and Aramis, and together they become involved in political intrigue, duels, and dangerous missions. The story emphasizes themes of loyalty, friendship, honor, and bravery.', 'bookIMG/the_three_musketeers.jpg', 45.00, 100, 'Action', 'English', 'Alexandre Dumas', 'Alexandre Dumas was known for his exciting storytelling, memorable characters, and dramatic plots.', 'images/authors/alexandre_dumas.jpg', 'text/english/the_three_musketeers.json', '2026-04-16 21:59:39'),
(37, 'Robin Hood', 'Robin Hood is a classic adventure story based on English folklore about a legendary outlaw who lives in Sherwood Forest. Robin Hood and his band of followers, including Little John and Friar Tuck, fight against injustice and help the poor by taking from the rich and giving to those in need. The story is known for its themes of bravery, fairness, friendship, and standing up against corruption.', 'bookIMG/robin_hood.jpg', 40.00, 100, 'Action', 'Arabic', 'Howard Pyle', 'Howard Pyle was an American writer and illustrator best known for his adventure stories and books for young readers. He wrote and illustrated many classic tales, especially stories about knights, pirates, and legendary heroes.', 'images/authors/howard_pyle.jpg', 'text/arabic/Robin_Hood.json', '2026-04-16 22:00:06'),
(38, 'The Prisoner of Zenda', 'The Prisoner of Zenda is an adventure novel about Rudolf Rassendyll, an English gentleman who travels to the fictional European kingdom of Ruritania. Because he looks exactly like the king, he is asked to impersonate him when the real king is kidnapped by his enemies. Rudolf must protect the kingdom while pretending to be the ruler, facing danger, political intrigue, and difficult personal choices.', 'bookIMG/the_prisoner_of_zenda.jpg', 45.00, 100, 'Action', 'English', 'Anthony Hope', 'Anthony Hope was an English novelist and playwright best known for his adventure and romantic novels. His novel The Prisoner of Zenda became his most famous work.', 'images/authors/anthony_hope.jpg', 'text/english/The_Prisoner_of_Zenda.json', '2026-04-16 22:00:06'),
(39, 'يوتوبيا', 'رواية بائسة تدور أحداثها في مصر المستقبلية حيث يعيش الأثرياء داخل مجتمع مسور، بينما يعيش الفقراء في فقر مدقع خارجه، مسلطة الضوء على الفجوة الاجتماعية الحادة.', 'bookIMG/ywtwbya.jpg', 40.00, 100, 'Sci-Fi', 'Arabic', 'أحمد خالد توفيق', 'طبيب وأستاذ وروائي مصري، يُنظر إليه على نطاق واسع باعتباره رائد أدب الرعب والخيال العلمي الحديث في العالم العربي.', 'images/authors/lahmd_khald_twfyq.jpg', 'text/arabic/يوتوبيا.json', '2026-04-16 22:00:06'),
(40, 'حوجن', 'رواية سعودية شهيرة تمزج بين الخيال العلمي والفانتازيا، وتتضمن قصة رومانسية تتعلق بكائن جنّي يعيش بين البشر ويقع في حب فتاة إنسانية، مما يخلق صراعًا بين العالمين.', 'bookIMG/hwjn.jpg', 40.00, 100, 'Sci-Fi', 'Arabic', 'إبراهيم عباس', 'كاتب سيناريو ومؤلف سعودي، اشتهر بروايته حوجن التي حققت مبيعات هائلة. يمزج عمله بين عناصر الخيال العلمي والفانتازيا العربية التقليدية.', 'images/authors/ibrahym_abas.jpg', 'text/arabic/حوجن.json', '2026-04-16 22:00:49'),
(41, 'القادمون', 'رواية خيال علمي وتشويق تدور أحداثها في عالم تسيطر عليه كيانات غامضة تُعرف باسم \"القادمون\"، حيث يستيقظ البطل ليجد نفسه في واقع مرعب ومختلف تمامًا عما يعرفه.', 'bookIMG/alqadmwn.jpg', 40.00, 100, 'Sci-Fi', 'Arabic', 'أحمد خالد مصطفى', 'كاتب وطبيب مصري، اشتهر برواياته التي تمزج بين الخيال العلمي والتشويق الفلسفي.', 'images/authors/ahmd_khald_mstfa.jpg', 'text/arabic/القادمون.json', '2026-04-16 22:00:49'),
(42, 'فرنكشتاين', 'رواية تمزج بين الرعب والخيال العلمي تعيد تفسير حكاية فرانكنشتاين في سياق معاصر يعكس الواقع السياسي والاجتماعي.', 'bookIMG/frnkshtayn.jpg', 40.00, 100, 'Sci-Fi', 'Arabic', 'أحمد سعداوي', 'روائي وشاعر وصحفي عراقي، فازت روايته فرنكشتاين في بغداد بالجائزة العالمية للرواية العربية (البوكر).', 'images/authors/ahmd_sadawy.jpg', 'text/arabic/فرنكشتاين.json', '2026-04-16 22:00:49'),
(43, 'الطابور', 'رواية بائسة بأسلوب كافكاوي تدور حول مواطنين ينتظرون في طابور لا نهاية له أمام سلطة غامضة، لتعكس موضوعي المراقبة والاستبداد.', 'bookIMG/altabwr.jpg', 40.00, 100, 'Sci-Fi', 'Arabic', 'بسمة عبد العزيز', 'روائية ونحاتة ومعالجة نفسية مصرية، لاقت روايتها الطابور استحسان النقاد لتصويرها الكافكاوي.', 'images/authors/bsma_abdalazyz.jpg', 'text/arabic/الطابور.json', '2026-04-16 22:00:49'),
(44, 'Dune', 'A massive space opera set in a far-future feudal society, revolving around a desert planet with rare resources, complex politics, and religion.', 'bookIMG/dune.jpg', 45.00, 100, 'Sci-Fi', 'English', 'Frank Herbert', 'An American journalist and author, Herbert is best known for Dune, one of the most influential science fiction novels.', 'images/authors/frank_herbert.jpg', 'text/english/Dune.json', '2026-04-16 22:00:49'),
(45, 'The Hitchhiker\'s Guide to the Galaxy', 'A comedic science fiction masterpiece that follows the misadventures of the last surviving human in space.', 'bookIMG/the_hitchhikers_guide_book.jpg', 45.00, 100, 'Sci-Fi', 'English', 'Douglas Adams', 'An English author and humorist known for blending science fiction with absurd comedy.', 'images/authors/douglas_adams.jpg', 'text/english/The_Hitchhikers_Guide_book.json', '2026-04-16 22:00:49'),
(46, 'The Martian', 'A gripping, science-heavy story of an astronaut accidentally abandoned on Mars who must use his ingenuity to survive.', 'bookIMG/the_martian.jpg', 45.00, 100, 'Sci-Fi', 'English', 'Andy Weir', 'An American author known for realistic science fiction, especially The Martian.', 'images/authors/andy_weir.jpg', 'text/english/The_Martian.json', '2026-04-16 22:00:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
