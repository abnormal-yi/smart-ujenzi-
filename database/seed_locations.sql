-- Tanzania Regions (31)
INSERT INTO regions (id, name) VALUES
(1, 'Arusha'),
(2, 'Dar es Salaam'),
(3, 'Dodoma'),
(4, 'Geita'),
(5, 'Iringa'),
(6, 'Kagera'),
(7, 'Katavi'),
(8, 'Kigoma'),
(9, 'Kilimanjaro'),
(10, 'Lindi'),
(11, 'Manyara'),
(12, 'Mara'),
(13, 'Mbeya'),
(14, 'Morogoro'),
(15, 'Mtwara'),
(16, 'Mwanza'),
(17, 'Njombe'),
(18, 'Pemba Kaskazini'),
(19, 'Pemba Kusini'),
(20, 'Pwani'),
(21, 'Rukwa'),
(22, 'Ruvuma'),
(23, 'Shinyanga'),
(24, 'Simiyu'),
(25, 'Singida'),
(26, 'Songwe'),
(27, 'Tabora'),
(28, 'Tanga'),
(29, 'Unguja Kaskazini'),
(30, 'Unguja Kusini'),
(31, 'Unguja Mjini Magharibi');

-- Tanzania Districts (by region)
INSERT INTO districts (region_id, name) VALUES
-- Arusha (1)
(1, 'Arusha City'), (1, 'Arusha Rural'), (1, 'Karatu'), (1, 'Longido'), (1, 'Meru'), (1, 'Monduli'), (1, 'Ngorongoro'),
-- Dar es Salaam (2)
(2, 'Ilala'), (2, 'Kigamboni'), (2, 'Kinondoni'), (2, 'Temeke'), (2, 'Ubungo'),
-- Dodoma (3)
(3, 'Bahi'), (3, 'Chamwino'), (3, 'Chemba'), (3, 'Dodoma City'), (3, 'Kondoa'), (3, 'Kongwa'), (3, 'Mpwapwa'),
-- Geita (4)
(4, 'Bukombe'), (4, 'Chato'), (4, 'Geita'), (4, 'Mbogwe'), (4, 'Nyang\'hwale'),
-- Iringa (5)
(5, 'Iringa Rural'), (5, 'Iringa Municipal'), (5, 'Kilolo'), (5, 'Mufindi'),
-- Kagera (6)
(6, 'Biharamulo'), (6, 'Bukoba Municipal'), (6, 'Bukoba Rural'), (6, 'Karagwe'), (6, 'Kyerwa'), (6, 'Missenyi'), (6, 'Muleba'), (6, 'Ngara'),
-- Katavi (7)
(7, 'Mlele'), (7, 'Mpanda'), (7, 'Mpimbwe'), (7, 'Nkasi'), (7, 'Tanganyika'),
-- Kigoma (8)
(8, 'Buhigwe'), (8, 'Kakonko'), (8, 'Kasulu'), (8, 'Kigoma Municipal'), (8, 'Kigoma Rural'), (8, 'Kibondo'), (8, 'Uvinza'),
-- Kilimanjaro (9)
(9, 'Hai'), (9, 'Moshi Municipal'), (9, 'Moshi Rural'), (9, 'Mwanga'), (9, 'Rombo'), (9, 'Same'), (9, 'Siha'),
-- Lindi (10)
(10, 'Kilwa'), (10, 'Lindi Municipal'), (10, 'Lindi Rural'), (10, 'Liwale'), (10, 'Nachingwea'), (10, 'Ruangwa'),
-- Manyara (11)
(11, 'Babati'), (11, 'Hanang'), (11, 'Kiteto'), (11, 'Mbulu'), (11, 'Simanjiro'),
-- Mara (12)
(12, 'Bunda'), (12, 'Butiama'), (12, 'Musoma Municipal'), (12, 'Musoma Rural'), (12, 'Rorya'), (12, 'Serengeti'), (12, 'Tarime'),
-- Mbeya (13)
(13, 'Busokelo'), (13, 'Chunya'), (13, 'Kyela'), (13, 'Mbarali'), (13, 'Mbeya City'), (13, 'Mbeya Rural'), (13, 'Rungwe'),
-- Morogoro (14)
(14, 'Gairo'), (14, 'Kilombero'), (14, 'Kilosa'), (14, 'Morogoro Municipal'), (14, 'Morogoro Rural'), (14, 'Mvomero'), (14, 'Ulanga'),
-- Mtwara (15)
(15, 'Masasi'), (15, 'Mtwara Municipal'), (15, 'Mtwara Rural'), (15, 'Nanyumbu'), (15, 'Newala'), (15, 'Tandahimba'),
-- Mwanza (16)
(16, 'Ilemela'), (16, 'Kwimba'), (16, 'Magu'), (16, 'Misungwi'), (16, 'Nyamagana'), (16, 'Sengerema'), (16, 'Ukerewe'),
-- Njombe (17)
(17, 'Ludewa'), (17, 'Makambako'), (17, 'Makete'), (17, 'Njombe'), (17, 'Wanging\'ombe'),
-- Pemba Kaskazini (18)
(18, 'Micheweni'), (18, 'Wete'),
-- Pemba Kusini (19)
(19, 'Chake Chake'), (19, 'Mkoani'),
-- Pwani (20)
(20, 'Bagamoyo'), (20, 'Kibaha'), (20, 'Kisarawe'), (20, 'Mkuranga'), (20, 'Rufiji'),
-- Rukwa (21)
(21, 'Kalambo'), (21, 'Nkasi'), (21, 'Sumbawanga Municipal'), (21, 'Sumbawanga Rural'),
-- Ruvuma (22)
(22, 'Mbinga'), (22, 'Namtumbo'), (22, 'Nyasa'), (22, 'Songea Municipal'), (22, 'Songea Rural'), (22, 'Tunduru'),
-- Shinyanga (23)
(23, 'Kahama'), (23, 'Kishapu'), (23, 'Shinyanga Municipal'), (23, 'Shinyanga Rural'), (23, 'Ushetu'),
-- Simiyu (24)
(24, 'Bariadi'), (24, 'Busega'), (24, 'Itilima'), (24, 'Maswa'), (24, 'Meatu'),
-- Singida (25)
(25, 'Ikungi'), (25, 'Iramba'), (25, 'Manyoni'), (25, 'Mkalama'), (25, 'Singida Municipal'), (25, 'Singida Rural'),
-- Songwe (26)
(26, 'Ileje'), (26, 'Mbozi'), (26, 'Momba'), (26, 'Songwe'), (26, 'Tunduma'),
-- Tabora (27)
(27, 'Igunga'), (27, 'Kaliua'), (27, 'Nzega'), (27, 'Sikonge'), (27, 'Tabora Municipal'), (27, 'Urambo'), (27, 'Uyui'),
-- Tanga (28)
(28, 'Handeni'), (28, 'Kilindi'), (28, 'Korogwe'), (28, 'Lushoto'), (28, 'Mkinga'), (28, 'Muheza'), (28, 'Pangani'), (28, 'Tanga City'),
-- Unguja Kaskazini (29)
(29, 'Kaskazini A'), (29, 'Kaskazini B'),
-- Unguja Kusini (30)
(30, 'Kati'), (30, 'Kusini'),
-- Unguja Mjini Magharibi (31)
(31, 'Magharibi A'), (31, 'Magharibi B'), (31, 'Mjini');
