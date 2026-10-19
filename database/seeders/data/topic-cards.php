<?php

/*
| Topic packs: question-style cards grouped by what they are about, so a table can pick "football", "movies", "history" … when it creates a game
| (or "Random" for a bit of everything). Every card is for ALL modes and written to be family-safe.
|
| topic => [
|   'facts'     [intensity, statement, answer(bool), explanation, penalty]   True or false
|   'quickfire' [intensity, prompt, seconds]                                  a ten-second list challenge
|   'groupgame' [intensity, prompt]                                          everyone plays, round the table
|   'spotlight' [intensity, prompt, seconds]                                  a creative moment on the clock
|   'bluff'     [intensity, prompt]                                          say it true or invent it; the table calls bluff
| ]
| Keep facts evergreen (no "currently", no recent results) so they do not go out of date. Editable in GameSet → Cards (the Topic box).
*/
return [

    // ───────────────────────────── Football ─────────────────────────────
    'football' => [
        'facts' => [
            [1, 'A normal football match lasts 90 minutes, plus added time.', true, 'Two halves of 45 minutes each, and the referee adds time for stoppages.', 1],
            [1, 'The men\'s FIFA World Cup is held every two years.', false, 'It is held every four years.', 1],
            [1, 'Brazil has won the men\'s World Cup five times.', true, 'In 1958, 1962, 1970, 1994 and 2002.', 1],
            [1, 'Argentina won the 2022 World Cup in Qatar.', true, 'They beat France on penalties after a 3–3 draw.', 1],
            [1, 'A goalkeeper may pick up the ball when a teammate deliberately kicks it back to them.', false, 'That is the back-pass rule: they must play it with their feet.', 1],
            [1, 'A red card means the player is sent off.', true, 'And their team plays on with one player fewer.', 1],
            [2, 'A player can be offside from a throw-in.', false, 'There is no offside from a throw-in, a goal kick or a corner.', 1],
            [1, 'The Premier League is the top division of English football.', true, 'It has 20 clubs.', 1],
            [2, 'Real Madrid has won more European Cup / Champions League titles than any other club.', true, 'They had 15 by 2024.', 1],
            [2, 'Pelé won three World Cups as a player.', true, 'In 1958, 1962 and 1970.', 1],
            [2, 'The first men\'s World Cup was played in 1930 in Uruguay.', true, 'Uruguay won it too.', 1],
            [1, 'Kenya has qualified for a men\'s FIFA World Cup.', false, 'Kenya has never qualified for the men\'s World Cup.', 1],
            [2, 'Morocco was the first African team to reach a World Cup semi-final.', true, 'They did it in 2022.', 1],
            [1, 'The Golden Boot at the World Cup goes to the tournament\'s top goalscorer.', true, 'It is also called the Golden Shoe.', 1],
            [2, 'A penalty spot is 11 metres (12 yards) from the goal line.', true, 'It is also 12 yards, in the old measure.', 1],
            [1, 'The goalkeeper must wear the same colour as their teammates.', false, 'Goalkeepers wear a different colour so the referee can tell them apart.', 1],
            [1, 'Arsenal play their home matches at Old Trafford.', false, 'Old Trafford is Manchester United\'s home. Arsenal play at the Emirates Stadium.', 1],
            [2, 'Diego Maradona scored the "Hand of God" goal at the 1986 World Cup.', true, 'He later scored a brilliant solo goal in the same match against England.', 1],
            [1, 'Harambee Stars is the nickname of Kenya\'s national football team.', true, 'Harambee means "let us pull together".', 1],
            [1, 'Three goals by the same player in one match is called a hat-trick.', true, 'The name comes from cricket.', 1],
            [2, 'The ball counts as a goal as soon as it touches the goal line.', false, 'The whole ball must cross the line.', 1],
            [3, 'A football team can have up to 15 substitutes on the pitch at the same time.', false, 'Only 11 players are on the pitch for each team at any time.', 1],
        ],
        'quickfire' => [
            [1, 'Name five Premier League clubs. Ten seconds!', 10], [1, 'Name four African footballers.', 10], [1, 'Name four positions on a football pitch.', 8],
            [1, 'Name five countries that have won the World Cup.', 12], [2, 'Name five football stadiums.', 12], [2, 'Name five national teams from Africa.', 10],
            [2, 'Name four clubs from the Spanish league.', 10], [3, 'Name five footballers who have won the Ballon d\'Or.', 12],
        ],
        'groupgame' => [
            [1, 'Name a footballer, round the table, no repeats. First to stall pays.'], [1, 'Name a football club, round the table, no repeats. First to stall pays.'],
            [2, 'Name a country that has played at a World Cup, round the table. First to repeat or stall pays.'], [2, 'Name a football stadium, round the table. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Do your best goal celebration and say whose it is.', 15], [1, 'Commentate on {other} taking a penalty like it is the World Cup final.', 20],
            [2, 'Give a half-time team talk to your favourite club.', 20], [2, 'Pick the best player in the room and explain why, like a football pundit.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about the best football match you have ever watched. True story?'], [1, 'Describe the best goal you have ever scored. Real, or invented?'],
            [2, 'Name a famous player you have met or stood near. Is it true?'], [2, 'Tell the story of the day your team broke your heart. True or bluff?'],
        ],
    ],

    // ───────────────────────────── Movies & TV ─────────────────────────────
    'movies' => [
        'facts' => [
            [1, 'The Lion King is a Disney film.', true, 'The animated original came out in 1994.', 1],
            [2, 'Titanic won 11 Academy Awards.', true, 'It tied the record with Ben-Hur and The Return of the King.', 1],
            [1, 'The first Avengers team-up film is called Avengers: Endgame.', false, 'The Avengers came out in 2012. Endgame is the 2019 finale.', 1],
            [1, 'Wakanda is a made-up African country in Marvel\'s Black Panther.', true, 'It is hidden and rich in a metal called vibranium.', 1],
            [1, 'Shrek is a green ogre.', true, 'He lives in a swamp.', 1],
            [1, 'Bollywood is the nickname of the Hindi film industry based in Mumbai.', true, 'The name mixes Bombay and Hollywood.', 1],
            [1, 'Nollywood is the film industry of Nigeria.', true, 'It is one of the biggest in the world by number of films.', 1],
            [1, 'Elsa sings "Let It Go" in the film Frozen.', true, 'It won the Oscar for Best Original Song.', 1],
            [2, 'Lupita Nyong\'o won an Oscar for 12 Years a Slave.', true, 'She won Best Supporting Actress, and she grew up in Kenya.', 1],
            [2, 'The Godfather was released in 1972.', true, 'Marlon Brando played Vito Corleone.', 1],
            [1, 'Squid Game is a Korean series.', true, 'It became one of Netflix\'s most-watched shows.', 1],
            [1, 'In Toy Story, Woody is a cowboy doll and Buzz is a space ranger.', true, 'Buzz Lightyear.', 1],
            [2, 'In Jurassic Park, the dinosaurs were brought back using DNA from mosquitoes trapped in amber.', true, 'That is the idea in the story.', 1],
            [1, 'James Bond\'s secret agent number is 007.', true, 'The licence to kill.', 1],
            [1, 'The Oscars are awards given only for music.', false, 'They honour films: acting, directing, writing and more.', 1],
            [1, 'Dory, from Finding Nemo, has trouble remembering things.', true, 'She has short-term memory loss.', 1],
            [2, 'Netflix started as a service that mailed DVDs to people.', true, 'Streaming came later.', 1],
            [2, '"Nairobi Half Life" (2012) was a Kenyan film.', true, 'It was Kenya\'s submission for the Oscars\' foreign-language category.', 1],
            [2, 'Black Panther was released in 2018.', true, 'Chadwick Boseman played the lead.', 1],
            [2, 'In the Star Wars films, Darth Vader is Luke Skywalker\'s father.', true, 'The famous twist in The Empire Strikes Back.', 1],
        ],
        'quickfire' => [
            [1, 'Name five Disney movies. Ten seconds!', 10], [1, 'Name five Marvel superheroes.', 10], [1, 'Name four animated movies.', 10],
            [1, 'Name five famous movie villains.', 10], [2, 'Name four actors who have played James Bond.', 10], [2, 'Name five Netflix series.', 10],
            [2, 'Name five movies with a number in the title.', 12], [2, 'Name four movies set in Africa.', 12],
        ],
        'groupgame' => [
            [1, 'Name a movie, round the table, no repeats. First to stall pays.'], [1, 'Name an actor or actress, round the table, no repeats. First to stall pays.'],
            [2, 'Name a movie that starts with the last letter of the previous movie. First to stall pays.'], [2, 'Name a TV show, round the table, no repeats. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Act out a famous movie scene without words. The table guesses.', 25], [1, 'Do your best movie-trailer voice to describe what you did today.', 20],
            [2, 'Pitch a sequel to your favourite movie in 20 seconds.', 25], [2, 'Do your best impression of a famous movie character.', 20],
        ],
        'bluff' => [
            [1, 'Tell us about the first movie you remember seeing in a cinema. True story?'], [1, 'Describe the worst movie you have ever sat through. Real, or invented?'],
            [2, 'Say which famous actor you think looks like someone here, and who. True or bluff?'], [2, 'Tell us about a movie that made you cry. Did it really?'],
        ],
    ],

    // ───────────────────────────── Music ─────────────────────────────
    'music' => [
        'facts' => [
            [1, 'A standard guitar has six strings.', true, 'Standard tuning is E-A-D-G-B-E.', 1],
            [1, 'Michael Jackson is known as the King of Pop.', true, 'Thriller is one of the best-selling albums ever.', 1],
            [1, 'Beyoncé was part of the group Destiny\'s Child.', true, 'Before her solo career.', 1],
            [1, 'Bob Marley was from Jamaica.', true, 'He made reggae famous around the world.', 1],
            [1, 'A piano has 88 keys.', true, '52 white and 36 black.', 1],
            [1, 'Sauti Sol is a Kenyan band.', true, 'They are from Nairobi.', 1],
            [1, 'Burna Boy is a Nigerian artist.', true, 'He is one of Afrobeats\' biggest stars.', 1],
            [1, 'The Beatles came from Liverpool.', true, 'In England.', 1],
            [2, 'Afrobeats (with an s) grew out of West Africa, mainly Nigeria and Ghana.', true, 'It is different from Fela Kuti\'s Afrobeat, which came first.', 1],
            [2, 'Benga is a popular Kenyan music style.', true, 'It started in western Kenya.', 1],
            [1, '"Jambo Bwana" is sung in Swahili.', true, 'Famously recorded by the Kenyan group Them Mushrooms.', 1],
            [1, 'A trio has four members.', false, 'A trio has three. A quartet has four.', 1],
            [1, 'Drake is Canadian.', true, 'From Toronto.', 1],
            [1, 'Rihanna comes from Barbados.', true, 'In the Caribbean.', 1],
            [1, 'A violin has more strings than a guitar.', false, 'A violin has four and a guitar usually has six.', 1],
            [2, '"A cappella" means singing without instruments.', true, 'It comes from the Italian for "in the style of the chapel".', 1],
            [1, 'Elvis Presley was nicknamed the King of Rock and Roll.', true, 'He was born in Mississippi.', 1],
            [2, 'Gengetone is a Kenyan music style that mixes Sheng lyrics with dancehall and hip-hop beats.', true, 'It took off in Nairobi.', 1],
            [2, 'Nyashinski was a member of the Kenyan group Kleptomaniax.', true, 'Before going solo.', 1],
            [1, 'Shakira is from Colombia.', true, 'She sang the 2010 World Cup anthem "Waka Waka".', 1],
        ],
        'quickfire' => [
            [1, 'Name five singers. Ten seconds!', 10], [1, 'Name five Kenyan musicians.', 12], [1, 'Name four musical instruments.', 8],
            [1, 'Name five songs with a colour in the title.', 12], [2, 'Name five Nigerian artists.', 12], [2, 'Name four bands.', 10],
            [2, 'Name five songs by the same artist.', 12], [2, 'Name four albums.', 12],
        ],
        'groupgame' => [
            [1, 'Name a singer, round the table, no repeats. First to stall pays.'], [1, 'Name a song, round the table, no repeats. First to stall pays.'],
            [2, 'Name a song by the same artist as the last one, then switch artist when someone stalls.'], [2, 'Name a Kenyan song or artist, round the table. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Sing the chorus of the song that is stuck in your head right now.', 20], [1, 'Play your best air guitar solo.', 15],
            [2, 'Rap your name and one thing about you for 15 seconds.', 20], [2, 'Do your best impression of a famous singer.', 20],
        ],
        'bluff' => [
            [1, 'Tell us about the first concert you ever went to. True story?'], [1, 'Describe the song that reminds you of a special moment. Real, or invented?'],
            [2, 'Tell us about the worst singing performance you have ever given. True or bluff?'], [2, 'Name a famous musician you have almost met. Did it happen?'],
        ],
    ],

    // ───────────────────────────── World & news ─────────────────────────────
    'world' => [
        'facts' => [
            [1, 'The United Nations headquarters is in New York.', true, 'On the East River in Manhattan.', 1],
            [1, 'There are 54 countries in Africa recognised by the United Nations.', true, 'It is the continent with the most countries.', 1],
            [2, 'The African Union is headquartered in Addis Ababa.', true, 'The capital of Ethiopia.', 1],
            [1, 'The Nile flows north into the Mediterranean Sea.', true, 'It is one of the longest rivers in the world.', 1],
            [1, 'Mount Kilimanjaro is in Kenya.', false, 'It is in Tanzania, though you can see it from Kenya.', 1],
            [1, 'Sydney is the capital of Australia.', false, 'The capital is Canberra.', 1],
            [1, 'Russia is the largest country in the world by area.', true, 'It spans eleven time zones.', 1],
            [2, 'Every country in the European Union uses the euro.', false, 'Several members keep their own currencies.', 1],
            [1, 'The Great Wall of China can be seen from the Moon with the naked eye.', false, 'It is far too narrow to see from the Moon.', 1],
            [2, 'The World Health Organization is the United Nations\' health agency.', true, 'Its headquarters are in Geneva.', 1],
            [1, 'The Sahara is the largest hot desert in the world.', true, 'It covers much of North Africa.', 1],
            [1, 'Brazil\'s capital city is Rio de Janeiro.', false, 'It is Brasília.', 1],
            [1, 'Vatican City is the smallest country in the world.', true, 'It lies inside Rome.', 1],
            [1, 'Lake Victoria is the largest lake in Africa.', true, 'Kenya, Uganda and Tanzania share it.', 1],
            [2, 'Antarctica has no permanent residents.', true, 'Only scientists and support staff live there, and many leave in winter.', 1],
            [1, 'The United States has 50 states.', true, 'The newest, Hawaii, joined in 1959.', 1],
            [2, 'The G20 is a group of major economies.', true, 'The African Union is a member too.', 1],
            [1, 'The currency of the United Kingdom is the pound sterling.', true, 'Written £.', 1],
            [2, 'Kenya gained independence from Britain in 1963.', true, 'On 12 December 1963.', 1],
            [2, 'The Amazon rainforest is mostly in Brazil.', true, 'It spans nine countries.', 1],
        ],
        'quickfire' => [
            [1, 'Name five African capital cities. Ten seconds!', 12], [1, 'Name five countries in Europe.', 10], [1, 'Name four continents.', 8],
            [1, 'Name five countries in Asia.', 10], [2, 'Name five rivers.', 10], [2, 'Name four countries that border Kenya.', 10],
            [2, 'Name five world capitals that start with the letter B, M or S.', 15], [2, 'Name four oceans or seas.', 8],
        ],
        'groupgame' => [
            [1, 'Name a country, round the table, no repeats. First to stall pays.'], [1, 'Name a capital city, round the table, no repeats. First to stall pays.'],
            [2, 'Name a country that starts with the last letter of the previous country. First to stall pays.'], [2, 'Name a famous landmark, round the table. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Read out a made-up breaking-news headline about someone in the room, like a TV newsreader.', 20], [1, 'Describe your dream trip around the world in 20 seconds.', 20],
            [2, 'Be a news reporter live from your own living room.', 20], [2, 'Give a speech as the leader of a made-up country.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about the farthest place you have ever travelled to. True story?'], [1, 'Describe a country you would love to visit and why. Real, or invented?'],
            [2, 'Tell us about a time you were on the news or in a newspaper. True or bluff?'], [2, 'Say which language other than your own you can say a few words in. Do you really?'],
        ],
    ],

    // ───────────────────────────── History ─────────────────────────────
    'history' => [
        'facts' => [
            [1, 'The pyramids of Giza were built in ancient Egypt.', true, 'More than 4,000 years ago.', 1],
            [1, 'World War II ended in 1945.', true, 'The war in Europe ended in May, and in Asia in September.', 1],
            [1, 'Nelson Mandela became South Africa\'s first democratically elected president in 1994.', true, 'After 27 years in prison.', 1],
            [1, 'Jomo Kenyatta was Kenya\'s first president.', true, 'He took office in 1964.', 1],
            [1, 'The Titanic sank on its first voyage.', true, 'In April 1912.', 1],
            [1, 'Christopher Columbus reached the Americas in 1492.', true, 'Sailing for Spain.', 1],
            [2, 'The Berlin Wall fell in 1989.', true, 'On 9 November.', 1],
            [2, 'Cleopatra lived closer in time to the Moon landing than to the building of the Great Pyramid.', true, 'The pyramid is about 2,500 years older than her; the Moon landing about 2,000 years after.', 1],
            [2, 'The Mau Mau uprising took place in colonial Kenya in the 1950s.', true, 'It was a key step towards independence.', 1],
            [1, 'The ancient Olympic Games began in Greece.', true, 'At Olympia, around 776 BC.', 1],
            [1, 'Queen Elizabeth II was the longest-reigning British monarch.', true, 'She reigned for 70 years.', 1],
            [2, 'Napoleon was unusually short.', false, 'He was about average height for his time. The myth came from different units and cartoons.', 1],
            [1, 'Neil Armstrong was the first person to walk on the Moon.', true, 'In July 1969.', 1],
            [2, 'The printing press was invented in Europe by Johannes Gutenberg around 1440.', true, 'It spread books and ideas.', 1],
            [2, 'The Kingdom of Aksum was in what is now Ethiopia and Eritrea.', true, 'It was a major trading power.', 1],
            [2, 'Mansa Musa, the emperor of Mali, is often called one of the richest people in history.', true, 'He travelled to Mecca in 1324 with enormous wealth.', 1],
            [1, 'Vikings wore horned helmets in battle.', false, 'There is no evidence they did. The idea came from 19th-century art.', 1],
            [1, 'The French Revolution began in 1789.', true, 'With the storming of the Bastille.', 1],
            [2, 'Kenya became a republic in 1964.', true, 'A year after independence.', 1],
            [2, 'Wangari Maathai won the Nobel Peace Prize in 2004.', true, 'She was the first African woman to win it.', 1],
            [3, 'The Great Fire of London happened in 1666.', true, 'It destroyed much of the old city.', 1],
        ],
        'quickfire' => [
            [1, 'Name four ancient civilisations. Ten seconds!', 10], [1, 'Name five famous kings or queens.', 12], [2, 'Name five wars.', 10],
            [2, 'Name four African empires or kingdoms.', 12], [2, 'Name five famous explorers.', 10], [2, 'Name four presidents of any country.', 10],
            [1, 'Name five inventions that changed the world.', 12], [3, 'Name five events from the 20th century.', 12],
        ],
        'groupgame' => [
            [1, 'Name a famous person from history, round the table, no repeats. First to stall pays.'], [1, 'Name an invention, round the table, no repeats. First to stall pays.'],
            [2, 'Name a war or a battle, round the table. First to stall pays.'], [2, 'Name an ancient wonder or monument, round the table. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Be a tour guide at a museum and describe a made-up ancient object.', 20], [1, 'Give a speech as an ancient king or queen to your people.', 20],
            [2, 'Act out a famous moment from history without words. The table guesses.', 25], [2, 'Tell the whole story of any famous battle in 20 seconds.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about a historical place you have visited. True story?'], [1, 'Describe which period of history you would travel back to, and why. Real, or invented?'],
            [2, 'Tell us what your grandparents told you about the old days. True or bluff?'], [2, 'Name a famous historical figure you are related to. Is it true?'],
        ],
    ],

    // ───────────────────────────── Did you know? ─────────────────────────────
    'didyouknow' => [
        'facts' => [
            [1, 'Octopuses have three hearts.', true, 'Two pump blood to the gills and one to the rest of the body.', 1],
            [1, 'Bananas grow on trees.', false, 'They grow on a giant plant whose "trunk" is made of tightly packed leaves.', 1],
            [2, 'Sharks are older than trees.', true, 'Sharks are around 450 million years old; trees about 350 million.', 1],
            [2, 'Lightning is hotter than the surface of the Sun.', true, 'About five times hotter.', 1],
            [1, 'A group of flamingos is called a flamboyance.', true, 'A good name for pink birds.', 1],
            [2, 'Sloths can hold their breath longer than dolphins.', true, 'Sloths can slow their heart rate and stay under for up to 40 minutes.', 1],
            [2, 'The shortest war in history lasted under an hour.', true, 'The Anglo-Zanzibar War of 1896 lasted about 40 minutes.', 1],
            [1, 'Wombat poop is cube-shaped.', true, 'It stops it from rolling away.', 1],
            [1, 'Cats can taste sweetness.', false, 'Cats cannot taste sweet things.', 1],
            [2, 'The unicorn is Scotland\'s national animal.', true, 'It has been on the royal coat of arms for centuries.', 1],
            [1, 'Peanuts are botanically nuts.', false, 'They are legumes, like beans and peas.', 1],
            [1, 'A strawberry has its seeds on the outside.', true, 'About 200 of them.', 1],
            [1, 'Goldfish only remember things for three seconds.', false, 'They can remember things for months.', 1],
            [2, 'Humans have more than five senses.', true, 'Balance, temperature and pain are senses too.', 1],
            [2, 'Mount Everest is the highest mountain measured from sea level.', true, 'Mauna Kea in Hawaii is taller from its base on the ocean floor.', 1],
            [2, 'Venus is the hottest planet in our solar system.', true, 'Hotter even than Mercury, because of its thick atmosphere.', 1],
            [2, 'A polar bear\'s skin is black.', true, 'Its fur only looks white.', 1],
            [2, 'Crows can recognise human faces.', true, 'And they remember who was unkind to them.', 1],
            [1, 'Elephants can jump.', false, 'They are among the few mammals that cannot.', 1],
            [3, 'A day on Earth is exactly 24 hours long.', false, 'It is about 23 hours 56 minutes for one spin; the 24 hours counts the Sun\'s position.', 1],
        ],
        'quickfire' => [
            [1, 'Name five animals that live in the sea. Ten seconds!', 10], [1, 'Name five things that are blue.', 10], [1, 'Name five animals with four legs.', 10],
            [2, 'Name four animals that can fly.', 10], [2, 'Name five things you find in space.', 10], [2, 'Name five planets or moons.', 10],
            [2, 'Name four animals that are nocturnal.', 10], [2, 'Name five types of weather.', 8],
        ],
        'groupgame' => [
            [1, 'Everybody shares a fun fact they know. Anyone who cannot think of one pays.'], [1, 'Name an animal, round the table, no repeats. First to stall pays.'],
            [2, 'Name an animal that starts with the last letter of the previous one. First to stall pays.'], [2, 'Name something you can find in a forest, round the table. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Make up a fun fact and say it as if you were an expert.', 20], [1, 'Describe the strangest animal you know.', 20],
            [2, 'Present a made-up "Did you know?" show for 15 seconds.', 20], [2, 'Be a nature-documentary narrator and describe {other}.', 20],
        ],
        'bluff' => [
            [1, 'Tell us the strangest fact you know. True or invented?'], [1, 'Describe the weirdest animal you have ever seen in real life. Real, or invented?'],
            [2, 'Say something you learned recently that surprised you. True or bluff?'], [2, 'Tell us about a record you once nearly broke. Is it real?'],
        ],
    ],

    // ───────────────────────────── Science & nature ─────────────────────────────
    'science' => [
        'facts' => [
            [1, 'Water boils at 100°C at sea level.', true, 'It boils at a lower temperature higher up a mountain.', 1],
            [1, 'The Sun is a star.', true, 'The closest one to Earth.', 1],
            [1, 'Adult humans have 206 bones.', true, 'Babies are born with about 300 that fuse together.', 1],
            [1, 'The chemical symbol for gold is Au.', true, 'From the Latin "aurum".', 1],
            [1, 'Sound travels faster than light.', false, 'Light is far faster, which is why you see lightning before you hear thunder.', 1],
            [2, 'Plants take in oxygen and give out carbon dioxide when they make food.', false, 'They take in carbon dioxide and release oxygen.', 1],
            [2, 'Mount Kenya is the second-highest mountain in Africa.', true, 'After Kilimanjaro.', 1],
            [1, 'Mercury is the planet closest to the Sun.', true, 'It is also the smallest.', 1],
            [1, 'A spider is an insect.', false, 'Spiders are arachnids and have eight legs.', 1],
            [1, 'Diamonds are made of carbon.', true, 'The same element as pencil lead.', 1],
            [1, 'Whales are fish.', false, 'They are mammals and breathe air.', 1],
            [2, 'The Great Rift Valley runs through Kenya.', true, 'It stretches thousands of kilometres across East Africa.', 1],
            [1, 'Bats are blind.', false, 'They can see, and many also use echolocation.', 1],
            [2, 'Sunlight takes about 8 minutes to reach Earth.', true, 'About 8 minutes and 20 seconds.', 1],
            [1, 'The Moon makes its own light.', false, 'It reflects light from the Sun.', 1],
            [1, 'Adults normally have 32 teeth.', true, 'Including the wisdom teeth.', 1],
            [1, 'Blood inside your veins is blue.', false, 'It is always red; veins just look blue through skin.', 1],
            [1, 'The cheetah is the fastest land animal.', true, 'Over short distances it can reach about 100 km/h.', 1],
            [2, 'Gravity on the Moon is weaker than on Earth.', true, 'About one sixth of Earth\'s.', 1],
            [3, 'The human body is about 60% water.', true, 'Roughly, for an adult.', 1],
        ],
        'quickfire' => [
            [1, 'Name five planets. Ten seconds!', 10], [1, 'Name five parts of the human body that start with the letter H, E or S.', 12], [1, 'Name four states of matter or forms of water.', 10],
            [2, 'Name five chemical elements.', 10], [2, 'Name five animals that are mammals.', 10], [2, 'Name five things that conduct electricity.', 12],
            [2, 'Name four colours of the rainbow.', 8], [3, 'Name five scientists.', 12],
        ],
        'groupgame' => [
            [1, 'Name a planet or moon, round the table, no repeats. First to stall pays.'], [1, 'Name a part of the body, round the table, no repeats. First to stall pays.'],
            [2, 'Name a scientific invention, round the table. First to stall pays.'], [2, 'Name a mammal, round the table, no repeats. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Explain how rain happens as if you were a teacher.', 20], [1, 'Explain how a rainbow forms in your own words.', 20],
            [2, 'Make up a science experiment and describe it with total confidence.', 25], [2, 'Describe life on another planet as if you had just visited it.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about a science experiment you did at school. True story?'], [1, 'Describe the most amazing thing you have ever seen in nature. Real, or invented?'],
            [2, 'Say what you wanted to be when you were a child, in science. True or bluff?'], [2, 'Tell us about a time you saw a wild animal up close. Did it happen?'],
        ],
    ],

    // ───────────────────────────── Kenya & Africa ─────────────────────────────
    'kenya' => [
        'facts' => [
            [1, 'Lake Turkana is the world\'s largest permanent desert lake.', true, 'It is nicknamed the Jade Sea.', 1],
            [1, 'Swahili is a national language of Kenya.', true, 'English is the other official language.', 1],
            [1, 'Mount Kenya is the highest mountain in Kenya.', true, 'It is the second-highest in Africa.', 1],
            [1, 'The Maasai Mara is famous for the annual wildebeest migration.', true, 'The herds move between the Serengeti and the Mara.', 1],
            [1, 'Mombasa is Kenya\'s main port city.', true, 'On the Indian Ocean coast.', 1],
            [2, 'Kenya has 47 counties.', true, 'Created by the 2010 constitution.', 1],
            [2, 'The Standard Gauge Railway links Mombasa and Nairobi.', true, 'The passenger train is called the Madaraka Express.', 1],
            [1, 'The equator passes through Kenya.', true, 'Near Nanyuki.', 1],
            [1, 'M-Pesa, the mobile money service, was launched in Kenya.', true, 'In 2007.', 1],
            [1, 'Lake Victoria is shared by Kenya, Uganda and Tanzania.', true, 'It is Africa\'s largest lake.', 1],
            [1, 'Africa is the second-largest continent by area.', true, 'After Asia.', 1],
            [1, 'Nigeria is the most populous country in Africa.', true, 'Ethiopia is second.', 1],
            [1, 'Harambee means "let us pull together".', true, 'It is also the national motto of Kenya.', 1],
            [1, 'The Serengeti is in Kenya.', false, 'It is in Tanzania; its Kenyan continuation is the Maasai Mara.', 1],
            [2, 'David Rudisha holds the men\'s 800 metres world record.', true, '1:40.91, set at the 2012 Olympics.', 1],
            [2, 'Faith Kipyegon has broken world records over middle distances.', true, 'She has set records at 1500 m and the mile.', 1],
            [1, 'Kenya is one of the world\'s biggest exporters of black tea.', true, 'Tea is a major export.', 1],
            [2, 'Nairobi is nicknamed the Silicon Savannah.', true, 'For its tech start-up scene.', 1],
            [1, 'The word "safari" comes from Swahili.', true, 'It means "journey".', 1],
            [2, 'Wangari Maathai started the Green Belt Movement in Kenya.', true, 'It has planted tens of millions of trees.', 1],
            [3, 'Kenya\'s flag has a traditional Maasai shield and two spears.', true, 'Black, red and green stripes, with white fimbriations.', 1],
        ],
        'quickfire' => [
            [1, 'Name five Kenyan towns or cities. Ten seconds!', 10], [1, 'Name five Kenyan foods.', 10], [1, 'Name four national parks in Kenya.', 10],
            [1, 'Name five animals you can see on a Kenyan safari.', 10], [2, 'Name five Kenyan athletes.', 12], [2, 'Name four African countries that border the Indian Ocean.', 12],
            [2, 'Name five Swahili words.', 10], [2, 'Name four rivers or lakes in Kenya.', 10],
        ],
        'groupgame' => [
            [1, 'Name a Kenyan town, round the table, no repeats. First to stall pays.'], [1, 'Name a Kenyan food or drink, round the table, no repeats. First to stall pays.'],
            [2, 'Say a Swahili word, round the table, no repeats. First to stall pays.'], [2, 'Name an African country, round the table, no repeats. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Describe your favourite Kenyan food so well that everybody is hungry.', 20], [1, 'Give a short speech welcoming visitors to Kenya.', 20],
            [2, 'Be a safari guide and describe the animals you can see right now.', 25], [2, 'Teach the table five Swahili words in 20 seconds.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about the best trip you have taken inside Kenya. True story?'], [1, 'Describe the best meal you have ever had at someone\'s home. Real, or invented?'],
            [2, 'Tell us about a famous Kenyan you have met. True or bluff?'], [2, 'Tell us the story behind your name. Is it really true?'],
        ],
    ],

    // ───────────────────────────── Sports ─────────────────────────────
    'sports' => [
        'facts' => [
            [1, 'A basketball team has five players on the court.', true, 'And one ball between ten players.', 1],
            [1, 'Wimbledon is played on grass courts.', true, 'In London, every summer.', 1],
            [1, 'Usain Bolt is from Jamaica.', true, 'He holds the 100 m and 200 m world records.', 1],
            [1, 'Rugby sevens has seven players per side.', true, 'Fast, short matches of seven minutes each half.', 1],
            [1, 'The Olympic flag has five rings.', true, 'They stand for five regions of the world.', 1],
            [2, 'A cricket T20 match has 20 overs per side.', true, 'It lasts about three hours.', 1],
            [2, 'A golfer who is one stroke under par on a hole has scored a "birdie".', true, 'Two under is an eagle.', 1],
            [1, 'The summer Olympics are held every four years.', true, 'The winter Games are held in between.', 1],
            [2, 'Nairobi hosted the All-Africa Games in 1987.', true, 'It was the fourth All-Africa Games.', 1],
            [1, 'Table tennis is also called ping-pong.', true, 'Played on a table with a small net.', 1],
            [1, 'A volleyball team has six players on the court.', true, 'Three at the net and three at the back.', 1],
            [2, 'Kenya won the Singapore Sevens in 2016.', true, 'It was the first time they won a World Series tournament.', 1],
            [2, 'Michael Phelps has won more Olympic gold medals than any other athlete.', true, 'Twenty-three of them.', 1],
            [1, 'A boxing ring is circular.', false, 'It is square.', 1],
            [2, 'Eliud Kipchoge won the Olympic marathon in 2016 and 2020.', true, 'Rio and Tokyo.', 1],
            [1, 'In basketball, a free throw is worth two points.', false, 'One point. Shots inside the arc are two and outside it three.', 1],
            [1, 'The Tour de France is a cycling race.', true, 'It lasts about three weeks.', 1],
            [1, 'A cricket team has 11 players on the field.', true, 'Plus the batters waiting.', 1],
            [2, 'Serena Williams won more than 20 Grand Slam singles titles.', true, 'Twenty-three.', 1],
            [3, 'The men\'s 100 metres world record is below 9.6 seconds.', true, 'Usain Bolt\'s 9.58.', 1],
        ],
        'quickfire' => [
            [1, 'Name five sports played with a ball. Ten seconds!', 10], [1, 'Name five Olympic sports.', 10], [1, 'Name four tennis players.', 10],
            [2, 'Name five athletes from Africa.', 12], [2, 'Name four basketball teams or players.', 10], [2, 'Name five sports played in water.', 10],
            [2, 'Name four countries that have hosted the Olympics.', 12], [2, 'Name five winter sports.', 10],
        ],
        'groupgame' => [
            [1, 'Name a sport, round the table, no repeats. First to stall pays.'], [1, 'Name an athlete, round the table, no repeats. First to stall pays.'],
            [2, 'Name a sport played with a bat or racket, round the table. First to stall pays.'], [2, 'Name a sports team, round the table, no repeats. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Do your best victory lap and say who you are thanking.', 15], [1, 'Commentate on {other} drinking water like it is a championship race.', 20],
            [2, 'Give the speech of an athlete who has just won gold.', 20], [2, 'Teach the table how to play any sport in 20 seconds.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about a sport you were really good at as a child. True story?'], [1, 'Describe the best sports match you have ever watched. Real, or invented?'],
            [2, 'Tell us about a time you won a medal or trophy. True or bluff?'], [2, 'Tell us about the worst sports injury you have had. Did it happen?'],
        ],
    ],

    // ───────────────────────────── Tech & gadgets ─────────────────────────────
    'tech' => [
        'facts' => [
            [1, 'WWW stands for World Wide Web.', true, 'Invented by Tim Berners-Lee around 1989.', 1],
            [1, 'The first iPhone was released in 2007.', true, 'It changed the smartphone.', 1],
            [2, 'Bluetooth is named after a Danish king.', true, 'Harald "Bluetooth" Gormsson.', 1],
            [1, 'M-Pesa lets people send money using a mobile phone.', true, 'It started in Kenya in 2007.', 1],
            [1, 'A byte is made of eight bits.', true, 'A bit is a 0 or a 1.', 1],
            [1, 'Emoji were first created in Japan.', true, 'The word means "picture character".', 1],
            [2, 'The first text message was sent in 1992.', true, 'It said "Merry Christmas".', 1],
            [1, 'USB stands for Universal Serial Bus.', true, 'The same plug for many devices.', 1],
            [1, 'Mark Zuckerberg co-founded Facebook.', true, 'It started at Harvard.', 1],
            [2, 'WhatsApp was bought by Facebook in 2014.', true, 'It is now owned by Meta.', 1],
            [2, 'The QWERTY keyboard is named after the first six letters on its top row.', true, 'It dates from the typewriter.', 1],
            [1, 'An email address always contains the @ symbol.', true, 'It separates the name from the website that hosts the mailbox.', 1],
            [2, 'The first computer mouse was made of wood.', true, 'Built by Douglas Engelbart in the 1960s.', 1],
            [1, '5G is slower than 4G.', false, '5G is designed to be much faster.', 1],
            [2, 'Ushahidi, a crisis-mapping tool, began in Kenya.', true, 'It was created in 2008.', 1],
            [1, 'Wikipedia is written and edited by volunteers.', true, 'Anyone can improve it.', 1],
            [2, 'Instagram started out as an app called Burbn.', true, 'It was a check-in app before photos took over.', 1],
            [1, 'Airplane mode turns off your phone\'s wireless signals.', true, 'It switches off calls, mobile data and usually Wi-Fi.', 1],
            [2, 'Netflix began as a DVD-by-mail company.', true, 'It started streaming in 2007.', 1],
            [3, 'The first website is still online.', true, 'The first website from CERN is preserved online.', 1],
        ],
        'quickfire' => [
            [1, 'Name five apps on your phone. Ten seconds!', 10], [1, 'Name five phone brands.', 10], [1, 'Name four social media platforms.', 8],
            [2, 'Name five websites you use every week.', 10], [2, 'Name four computer parts.', 10], [2, 'Name five video games.', 10],
            [2, 'Name four search engines or browsers.', 10], [2, 'Name five gadgets in your house.', 10],
        ],
        'groupgame' => [
            [1, 'Name an app, round the table, no repeats. First to stall pays.'], [1, 'Name a gadget, round the table, no repeats. First to stall pays.'],
            [2, 'Name a video game, round the table, no repeats. First to stall pays.'], [2, 'Name a tech company, round the table, no repeats. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Sell the table a new gadget you have just invented.', 20], [1, 'Do your best robot voice for 15 seconds.', 15],
            [2, 'Pitch a new app in 20 seconds. Name, idea and why people will love it.', 25], [2, 'Explain what the internet is to someone from 100 years ago.', 25],
        ],
        'bluff' => [
            [1, 'Tell us about the first phone you ever owned. True story?'], [1, 'Describe the funniest thing that has happened to you on your phone. Real, or invented?'],
            [2, 'Tell us about a time technology completely failed you. True or bluff?'], [2, 'Tell us the oldest photo you still have on your phone. Is it real?'],
        ],
    ],

    // ───────────────────────────── Food & drink ─────────────────────────────
    'food' => [
        'facts' => [
            [1, 'Ugali is made from maize flour.', true, 'A staple across East Africa.', 1],
            [1, 'Chocolate is made from cacao beans.', true, 'They grow inside pods.', 1],
            [2, 'Botanically, a tomato is a fruit.', true, 'It grows from a flower and holds seeds.', 1],
            [1, 'Pilau is a spiced rice dish popular on the Kenyan coast.', true, 'With cumin, cardamom and cloves.', 1],
            [1, 'Sushi always contains raw fish.', false, 'Many sushi rolls use cooked fish or vegetables.', 1],
            [1, 'Honey is made by bees from flower nectar.', true, 'They fan it with their wings to thicken it.', 1],
            [2, 'The hottest part of a chilli pepper is the seeds.', false, 'It is mostly the white pith around them.', 1],
            [1, 'Pineapples grow on trees.', false, 'They grow on a low plant, one fruit at a time.', 1],
            [1, '"Nyama choma" means grilled meat in Swahili.', true, 'A favourite weekend meal.', 1],
            [1, '"Sukuma wiki" means "push the week".', true, 'Because kale helps stretch the budget.', 1],
            [1, 'Mandazi is a fried dough snack in East Africa.', true, 'Often eaten with chai.', 1],
            [2, 'Coffee beans are the seeds of a fruit called a coffee cherry.', true, 'They are roasted before we brew them.', 1],
            [2, 'More than half of the world\'s people eat rice as a staple food.', true, 'It is one of the world\'s most important crops.', 1],
            [1, 'Avocados are fruit.', true, 'With one large seed.', 1],
            [2, 'Kenya is known for its coffee as well as its tea.', true, 'Kenyan coffee is prized around the world.', 1],
            [2, 'Chai in Kenya is usually tea brewed with milk.', true, 'Often with ginger or spices.', 1],
            [1, 'Spaghetti comes from Japan.', false, 'It is Italian, though noodles are made in many countries.', 1],
            [1, 'An apple a day is a real prescription.', false, 'It is just a saying about healthy eating.', 1],
            [2, 'Wheat flour is used to make chapati.', true, 'Rolled, layered and cooked on a pan.', 1],
            [3, 'Watermelon is mostly water.', true, 'About 92 percent.', 1],
        ],
        'quickfire' => [
            [1, 'Name five fruits. Ten seconds!', 10], [1, 'Name five Kenyan dishes.', 10], [1, 'Name five things you can eat for breakfast.', 10],
            [1, 'Name five vegetables.', 10], [2, 'Name five types of rice or bread.', 10], [2, 'Name four street foods.', 10],
            [2, 'Name five spices.', 10], [2, 'Name five desserts.', 10],
        ],
        'groupgame' => [
            [1, 'Name a fruit, round the table, no repeats. First to stall pays.'], [1, 'Name a food that starts with the letter the roller says. First to stall pays.'],
            [2, 'Name a dish from another country, round the table. First to stall pays.'], [2, 'Name something you can put in a sandwich, round the table. First to stall pays.'],
        ],
        'spotlight' => [
            [1, 'Describe your favourite meal so well that everybody is hungry.', 20], [1, 'Do your best cooking-show host voice and make a sandwich in the air.', 20],
            [2, 'Pitch a new restaurant. Name, menu and why it is the best.', 25], [2, 'Review the last thing you ate like a food critic.', 20],
        ],
        'bluff' => [
            [1, 'Tell us about the best meal you have ever eaten. True story?'], [1, 'Describe the weirdest food you have ever tried. Real, or invented?'],
            [2, 'Tell us about a cooking disaster you caused. True or bluff?'], [2, 'Tell us what you can cook really well. Is it true?'],
        ],
    ],

    // ───────────────────────────── In the news (2026) ─────────────────────────────
    // A snapshot of big stories up to October 2026, checked against several news sources. Keep it fresh in GameSet → Cards (Topic: "In the news"):
    // add new cards, switch old ones off. Only results and events that were settled and agreed on across sources are used here.
    'news' => [
        'facts' => [
            [1, 'Spain won the 2026 men\'s World Cup.', true, 'They beat Argentina 1–0 after extra time in the final on 19 July 2026, at MetLife Stadium in New Jersey.', 1],
            [1, 'The 2026 World Cup final was decided by a penalty shootout.', false, 'Ferran Torres scored the only goal in extra time, so there was no shootout.', 1],
            [1, 'Argentina won the World Cup again in 2026.', false, 'Spain beat them in the final. Argentina had been the defending champions.', 1],
            [1, 'The 2026 World Cup was hosted by the United States, Canada and Mexico.', true, 'It was the first World Cup shared by three host countries.', 1],
            [2, 'The 2026 World Cup was the first with 48 teams.', true, 'Up from 32 teams at the 2022 tournament.', 1],
            [1, 'Paris Saint-Germain won the 2026 Champions League final on penalties.', true, 'They beat Arsenal 4–3 on penalties after a 1–1 draw in Budapest on 30 May 2026.', 1],
            [2, 'Arsenal have won the Champions League.', false, 'They have never won it. They lost the 2026 final on penalties.', 1],
            [1, 'Arsenal won the 2025–26 Premier League title.', true, 'It was their first league title in 22 years, and they finished on 85 points.', 1],
            [2, 'Liverpool kept the Premier League title in 2025–26.', false, 'Arsenal won it, and Liverpool finished fifth.', 1],
            [2, 'The New York Knicks won the 2026 NBA Finals.', true, 'They beat the San Antonio Spurs 4–1, their first title since 1973.', 1],
            [2, 'The Seattle Seahawks won Super Bowl LX in February 2026.', true, 'They beat the New England Patriots 29–13.', 1],
            [2, 'Norway topped the medal table at the 2026 Winter Olympics in Milano Cortina.', true, 'With 18 gold medals and 41 medals in all.', 1],
            [2, 'Jannik Sinner won the men\'s singles title at Wimbledon in 2026.', true, 'He defended his 2025 title.', 1],
            [2, 'Tadej Pogačar won his fifth Tour de France in 2026.', true, 'That ties the record of five wins held by Merckx, Hinault, Anquetil and Indurain.', 1],
            [2, 'Ousmane Dembélé won the 2025 Ballon d\'Or.', true, 'He was named the best men\'s player in September 2025, after PSG\'s treble.', 1],
            [1, 'A Kenyan runner ran a marathon in under two hours in an official race in 2026.', true, 'Sabastian Sawe won the London Marathon on 26 April 2026 in 1:59:30.', 1],
            [3, 'Before 2026, the official marathon world record belonged to Kelvin Kiptum.', true, 'His 2:00:35 in Chicago in 2023.', 1],
            [1, 'One Battle After Another won Best Picture at the 2026 Oscars.', true, 'Paul Thomas Anderson also won Best Director for it.', 1],
            [2, 'Michael B. Jordan won Best Actor at the 2026 Oscars for Sinners.', true, 'Sinners also set a record with 16 nominations.', 1],
            [2, 'KPop Demon Hunters won Best Animated Feature at the 2026 Oscars.', true, 'The film was a huge streaming hit.', 1],
            [2, 'Bad Bunny won Album of the Year at the 2026 Grammys.', true, 'He was the first artist to win it with a Spanish-language album.', 1],
            [2, 'Bulgaria won the Eurovision Song Contest 2026.', true, 'Dara\'s "Bangaranga" won in Vienna in May 2026.', 1],
            [2, 'The 2026 Nobel Peace Prize went to South African jurist Navi Pillay.', true, 'The former UN human rights chief was announced as the winner on 9 October 2026.', 1],
            [2, 'Artemis II landed astronauts on the Moon in 2026.', false, 'It flew four astronauts around the Moon and back without landing. They splashed down on 10 April 2026.', 1],
            [2, 'A Canadian astronaut flew around the Moon on Artemis II.', true, 'Jeremy Hansen of the Canadian Space Agency was one of the four crew.', 1],
            [2, 'SpaceX\'s stock market listing in June 2026 raised a record $75 billion.', true, 'It was the largest IPO in history.', 1],
            [2, 'Viktor Orbán lost Hungary\'s April 2026 election.', true, 'Péter Magyar\'s Tisza party won, ending Orbán\'s 16 years in power.', 1],
            [2, 'Pope Leo XIV\'s first encyclical, published in May 2026, is about artificial intelligence.', true, 'It is called Magnifica Humanitas.', 1],
            [2, 'Pope Leo XIV is the first pope from the United States.', true, 'He was elected in May 2025.', 1],
            [3, 'In May 2026 the WHO declared an Ebola outbreak in DR Congo and Uganda a public health emergency of international concern.', true, 'The declaration was made on 17 May 2026.', 1],
            [2, 'Kenya\'s next general election is set for 10 August 2027.', true, 'The electoral commission (IEBC) confirmed the date.', 1],
            [2, 'The US midterm elections are scheduled for 3 November 2026.', true, 'All seats in the House and about a third of the Senate are up for election.', 1],
            [3, 'Morocco hosted the 2025 Africa Cup of Nations, played from December 2025 to January 2026.', true, 'The final was played in Rabat.', 1],
        ],
        'quickfire' => [
            [1, 'Name five countries that played at the 2026 World Cup. Ten seconds!', 12], [1, 'Name four movies that came out in 2026.', 10],
            [1, 'Name five songs everyone has been hearing this year.', 12], [2, 'Name four big sports events of 2026.', 10],
            [2, 'Name five big stories you remember from the news this year.', 12], [2, 'Name four countries that held an election in 2026.', 12],
        ],
        'groupgame' => [
            [1, 'Name a country that was at the 2026 World Cup, round the table, no repeats. First to stall pays.'], [1, 'Name a song that was popular this year, round the table. First to stall pays.'],
            [2, 'Name a movie or series you watched this year, round the table, no repeats. First to stall pays.'], [2, 'Say one thing that made the news this year, round the table. Anyone who repeats pays.'],
        ],
        'spotlight' => [
            [1, 'Read a made-up headline about someone in the room like a TV news anchor.', 20], [1, 'Give the victory speech of the 2026 World Cup winners\' captain.', 20],
            [2, 'Be a sports reporter and recap the best game you saw this year.', 25], [2, 'Present the "news of the year" in 20 seconds.', 25],
        ],
        'bluff' => [
            [1, 'Tell us where you were when the 2026 World Cup final was played. True story?'], [1, 'Tell us the one news story from this year that you followed closely. Real, or invented?'],
            [2, 'Say which movie or series of 2026 you loved most, and why. True or bluff?'], [2, 'Tell us about a prediction you made this year that came true. Did it really?'],
        ],
    ],
];
