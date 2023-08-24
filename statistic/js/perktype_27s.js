const perktype = [
	{ name: 'Быстрый метаболизм', pid: '550', type: '' },
	{ name: 'Крушила', pid: '551', type: '' },
	{ name: 'Xилое тело', pid: '552', type: '' },
	{ name: 'Однорукий', pid: '553', type: '' },
	{ name: 'Точность', pid: '554', type: '' },
	{ name: 'Камикадзе', pid: '555', type: '' },
	{ name: 'Вор (трейт)', pid: '556', type: '' },
	{ name: 'Быстрый стрелок', pid: '557', type: '' },
	{ name: 'Маньяк', pid: '558', type: '' },
	{ name: 'Дурной глаз (трейт)', pid: '559', type: '' },
	{ name: 'Добродушие', pid: '560', type: '' },
	{ name: 'Химик', pid: '561', type: '' },
	{ name: 'Стабильный', pid: '562', type: '' },
	{ name: 'Жидкое тело', pid: '563', type: '' },
	{ name: 'Умелец', pid: '564', type: '' },
	{ name: 'Импульсивный', pid: '565', type: '' },
	{ name: 'Доп. рукопашн. атаки', pid: '302', type: 'lvl15' },
	{ name: 'Доп. рукопашн. повр.', pid: '303', type: 'lvl33' },
	{ name: 'Бонус движения', pid: '304', type: 'lvl3' },
	{ name: 'Бонус точности', pid: '305', type: 'lvl6' },
	{ name: 'Бонус скорости', pid: '306', type: 'lvl15' },
	{ name: 'Быстрая реакция', pid: '307', type: 'lvl3' },
	{ name: 'Быстрое лечение', pid: '308', type: 'lvl6' },
	{ name: 'Больше крит. атак', pid: '309', type: 'lvl3' },
	{ name: 'Иммунитет', pid: '310', type: 'lvl15' },
	{ name: 'Охранник', pid: '311', type: 'quest' },
	{ name: 'Уст. к радиации', pid: '312', type: 'lvl33' },
	{ name: 'Крутизна', pid: '313', type: 'lvl6' },
	{ name: 'Переноска', pid: '314', type: 'lvl3' },
	{ name: 'Меткость', pid: '315', type: 'lvl6' },
	{ name: 'Бесшумный бег', pid: '316', type: 'lvl15' },
	{ name: 'Исследователь', pid: '317', type: 'lvl33' },
	{ name: 'Торговля', pid: '318', type: 'lvl33' },
	{ name: 'Образование', pid: '319', type: 'lvl3' },
	{ name: 'Лечение', pid: '320', type: 'lvl9' },
	{ name: 'Воодушевление', pid: '321', type: 'lvl12' },
	{ name: 'Лучшие крит. атаки', pid: '322', type: 'lvl9' },
	{ name: 'Двуличный', pid: '323', type: 'lvl6' },
	{ name: 'Дробила', pid: '324', type: 'lvl15' },
	{ name: 'Снайпер', pid: '325', type: 'lvl15' },
	{ name: 'Хитрость', pid: '326', type: 'lvl15' },
	{ name: 'Человек действия', pid: '327', type: 'lvl6' },
	{ name: 'Стойкость', pid: '328', type: 'sys' },
	{ name: 'Сила жизни', pid: '329', type: 'lvl15' },
	{ name: 'Увертливость', pid: '330', type: 'lvl9' },
	{ name: 'Змееглот', pid: '331', type: 'lvl33' },
	{ name: 'Самоделкин', pid: '322', type: 'lvl30' },
	{ name: 'Медик', pid: '323', type: 'lvl30' },
	{ name: 'Вор-профессионал', pid: '334', type: 'lvl9' },
	{ name: 'Болтливость', pid: '335', type: 'lvl33' },
	{ name: 'Счастливчик', pid: '336', type: 'lvl9' },
	{ name: 'Фанат дробовиков', pid: '337', type: 'lvl6' },
	{ name: 'Карманник', pid: '338', type: 'lvl33' },
	{ name: 'Привидение', pid: '339', type: 'lvl6' },
	{ name: 'Отличник', pid: '340', type: 'lvl6' },
	{ name: 'Полевой медик', pid: '341', type: 'lvl12' },
	{ name: 'Непоседа', pid: '342', type: 'lvl6' },
	{ name: 'Полевой санитар', pid: '343', type: 'lvl6' },
	{ name: 'Следопыт', pid: '344', type: 'lvl30' },
	{ name: 'Удачный промах', pid: '345', type: 'lvl12' },
	{ name: 'Скаут', pid: '346', type: 'lvl33' },
	{ name: 'Ветеран', pid: '347', type: 'lvl12' },
	{ name: 'Рейнджер', pid: '348', type: 'lvl33' },
	{ name: 'Оптимизация', pid: '349', type: 'lvl3' },
	{ name: 'Опыт торговли', pid: '350', type: 'sys' },
	{ name: 'Самоучка', pid: '351', type: 'lvl3' },
	{ name: 'Математик', pid: '352', type: 'lvl15' },
	{ name: 'Мутация', pid: '353', type: 'lvl12' },
	{ name: 'Регенерация', pid: '354', type: 'lvl12' },
	{ name: 'Осведомленность', pid: '355', type: 'lvl3' },
	{ name: 'Крепкий Орешек', pid: '356', type: 'lvl12' },
	{ name: 'Эгоист', pid: '357', type: 'lvl3' },
	{ name: 'Ящерица', pid: '358', type: 'lvl9' },
	{ name: 'Крутой парень', pid: '359', type: 'lvl6' },
	{ name: 'Толстокожий', pid: '360', type: 'sys' },
	{ name: 'Мастер ближнего боя', pid: '361', type: 'lvl3' },
	{ name: 'Ковбой', pid: '362', type: 'lvl6' },
	{ name: 'Критическое мышление', pid: '363', type: 'lvl9' },
	{ name: 'Рикошет', pid: '364', type: 'lvl12' },
	{ name: 'Разгрузка', pid: '365', type: 'sys' },
	{ name: 'Стоик', pid: '366', type: 'lvl6' },
	{ name: 'Концентрация', pid: '367', type: 'lvl15' },
	{ name: 'Лидер', pid: '368', type: 'lvl12' },
	{ name: 'Авторитет', pid: '369', type: 'lvl15' },
	{ name: 'Зарядка', pid: '370', type: 'lvl33' },
	{ name: 'Рефлексы', pid: '371', type: 'lvl12' },
	{ name: 'Неудержимый', pid: '372', type: 'lvl15' },
	{ name: 'Закалка', pid: '373', type: 'lvl33' },
	{ name: 'Репликант', pid: '374', type: 'lvl30' },
	{ name: 'Сапер', pid: '375', type: 'sys' },
	{ name: 'Глаза и уши', pid: '376', type: 'lvl9' },
	{ name: 'Голем', pid: '377', type: 'lvl6' },
	{ name: 'Ярость', pid: '378', type: 'lvl6' },
	{ name: 'Броня', pid: '379', type: 'lvl12' },
	{ name: 'Кровопийца', pid: '380', type: 'lvl12' },
	{ name: 'Бдительность', pid: '381', type: 'lvl3' },
	{ name: 'Наблюдательность', pid: '382', type: 'lvl3' },
	{ name: 'Эксперт подрывник', pid: '383', type: 'lvl33' },
	{ name: 'Атлет', pid: '384', type: 'lvl6' },
	{ name: 'Получить силу', pid: '385', type: 'lvl6' },
	{ name: 'Получить восприятие', pid: '386', type: 'lvl6' },
	{ name: 'Получить выносливость', pid: '387', type: 'lvl6' },
	{ name: 'Получить обаяние', pid: '388', type: 'lvl6' },
	{ name: 'Получить интеллект', pid: '389', type: 'lvl6' },
	{ name: 'Получить ловкость', pid: '390', type: 'lvl6' },
	{ name: 'Получить удачу', pid: '391', type: 'lvl6' },
	{ name: 'Безвредность', pid: '392', type: 'lvl33' },
	{ name: 'Специалист', pid: '393', type: 'lvl33' },
	{ name: 'Верткость', pid: '394', type: 'lvl6' },
	{ name: 'Спортсмен', pid: '395', type: 'lvl12' },
	{ name: 'Исполнительность', pid: '396', type: 'quest' },
	{ name: 'Легкие шаги', pid: '397', type: 'lvl6' },
	{ name: 'Анатомия жизни', pid: '398', type: 'lvl15' },
	{ name: 'Привлекательность', pid: '399', type: 'lvl33' },
	{ name: 'Негоциант', pid: '400', type: 'lvl33' },
	{ name: 'Запаковка', pid: '401', type: 'lvl33' },
	{ name: 'Пироманьяк', pid: '402', type: 'lvl9' },
	{ name: 'Прыгучесть', pid: '403', type: 'lvl9' },
	{ name: 'Продажа', pid: '404', type: 'lvl33' },
	{ name: 'Человек-глыба', pid: '405', type: 'lvl6' },
	{ name: 'Вор', pid: '406', type: 'lvl30' },
	{ name: 'Обращение с оружием', pid: '407', type: 'lvl30' },
	{ name: 'Стажировка в Городе-Убежище', pid: '408', type: 'quest' },
	{ name: 'Спец по уборке экскрементов', pid: '417', type: 'sys' },
	{ name: 'Опытный медик', pid: '418', type: 'lvl6' },
	{ name: 'Дурной глаз(перк)', pid: '419', type: 'sys' },
	{ name: 'Терминатор', pid: '420', type: 'lvl15' },
	{ name: 'Взрывотехник', pid: '421', type: 'lvl12' },
	{ name: 'Токсиколог', pid: '422', type: 'lvl9' },
	{ name: 'Дополнительные атаки', pid: '423', type: 'lvl15' },
	{ name: 'Бывалый', pid: '424', type: 'lvl30' },
	{ name: 'Карман', pid: '425', type: 'lvl33' },
	{ name: 'Движение жизнь', pid: '426', type: 'lvl3' }, //to do
	{ name: 'Флаг удачи', pid: '427', type: 'lvl6' },
	{ name: 'Прицел', pid: '428', type: 'lvl6' },
	{ name: 'Выстрел', pid: '429', type: 'lvl6' }, //to do
	{ name: 'Скорняк', pid: '430', type: 'quest' },
	{ name: 'Прививки из Города-Убежище', pid: '431', type: 'quest' },
	{ name: 'Живчик', pid: '432', type: 'lvl12' },
	{ name: 'Улучшенная подкожная броня', pid: '433', type: 'quest' },
	{ name: 'Реаниматор', pid: '434', type: 'lvl12' },
	{ name: 'Улучшенная подкожная защита', pid: '434', type: 'quest' },
	{ name: 'Операция доктора Клауса:Крит', pid: '436', type: 'quest' },
	{ name: 'Операция доктора Клауса:Антикрит', pid: '437', type: 'quest' },
	{ name: 'Секреты мастерства:Бартер', pid: '438', type: 'quest' },
	{ name: 'Секреты мастерства:Ремонт', pid: '439', type: 'quest' },
	{ name: 'Золотые руки', pid: '440', type: 'lvl33' },
	{ name: 'Водитель', pid: '442', type: 'quest' },
	{ name: 'Офицер', pid: '443', type: 'lvl12' },
	{ name: 'Смотрящий', pid: '444', type: 'quest' },
	{ name: 'Быстрота', pid: '445', type: 'lvl3' },
	{ name: 'Боевой имплантант', pid: '446', type: 'imp' },
	{ name: 'Медицинский имплантант', pid: '447', type: 'imp' },
	{ name: 'Вспомогательный имплант', pid: '448', type: 'imp' },
	{ name: 'Боевой Инженер', pid: '449', type: 'lvl15' },
	{ name: 'Оператор чата', pid: '450', type: 'sys' },
	{ name: 'Мастер урона', pid: '451', type: 'mperk' },
	{ name: 'Мастер лазера', pid: '452', type: 'mperk' },
	{ name: 'Мастер огня', pid: '453', type: 'mperk' },
	{ name: 'Мастер плазмы', pid: '454', type: 'mperk' },
	{ name: 'Мастер электричества', pid: '455', type: 'mperk' },
	{ name: 'Мастер импульса', pid: '456', type: 'mperk' },
	{ name: 'Мастер взрыва', pid: '457', type: 'mperk' },
	{ name: 'Зоркий', pid: '458', type: 'lvl6' },
	{ name: 'Статист', pid: '459', type: 'sys' },
	{ name: 'Житель Пустоши', pid: '460', type: 'mperk' },
	{ name: 'Опытный ремонтник', pid: '461', type: 'mperk' },
	{ name: 'Опытный инженер', pid: '462', type: 'mperk' },
	{ name: 'Опытный врач', pid: '463', type: 'mperk' },
	{ name: 'Класс персонажа', pid: '465', type: 'sys' },
	{ name: 'Огневая поддержка', pid: '466', type: 'lvl9' }
];