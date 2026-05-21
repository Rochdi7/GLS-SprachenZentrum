document.addEventListener('DOMContentLoaded', () => {
// Center carousel data - in desired loop order
const centersInOrder = [
'rabat',
'casablanca',
'marrakech',
'kenitra',
'sale',
'agadir'
];

const centerData = {
rabat: {
label: 'Rabat',
map_iframe_src:
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3307.8001465016737!2d-6.8485901!3d33.9976668!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76dcf7a656da5%3A0xcaf46ae5e6e81d87!2sGLS%20Sprachenzentrum%20-%20Centre%20GLS%20de%20langue%20Allemande%20Rabat!5e0!3m2!1sen!2sma!4v1769193870895!5m2!1sen!2sma',
map_url: 'https://maps.app.goo.gl/mUnSAVYEnGToS8i2A'
},
casablanca: {
label: 'Casablanca',
map_iframe_src:
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3323.447592536715!2d-7.621097299999997!3d33.5936893!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda7d3157551b175%3A0x6ea42bcafb702001!2sGLS%20Sprachzentrum%20-%20Centre%20GLS%20de%20langue%20Allemande%20Casablanca!5e0!3m2!1sen!2sma!4v1769193811581!5m2!1sen!2sma',
map_url: 'https://maps.app.goo.gl/EdqBoa3KWEYjuzoq7'
},
marrakech: {
label: 'Marrakech',
map_iframe_src:
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3396.851254286348!2d-8.009762!3d31.637922800000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xdafefd52ae80051%3A0xf05642e7c0fe1300!2sGLS%20Sprachenzentrum%20-%20Centre%20de%20langue%20Allemande%20Marrakech!5e0!3m2!1sen!2sma!4v1769193827051!5m2!1sen!2sma',
map_url: 'https://maps.app.goo.gl/krR8pGZue3DW3yyv6'
},
kenitra: {
label: 'Kénitra',
map_iframe_src:
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3297.619221988769!2d-6.5876841!3d34.25825869999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda75970012d1755%3A0xb301a964fc17f669!2sGLS%20Sprachenzentrum%20-%20Centre%20GLS%20de%20langue%20Allemand%20K%C3%A9nitra!5e0!3m2!1sen!2sma!4v1769193796460!5m2!1sen!2sma',
map_url: 'https://maps.app.goo.gl/pEsso9L8ygWpdSor5'
},
sale: {
label: 'Salé',
map_iframe_src:
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3306.1478897952475!2d-6.8172275!3d34.0400773!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda76b254ea656d5%3A0xaf2f9258ee6fba89!2sGls%20Sprachenzentrum%20Centre%20D\'allemand!5e0!3m2!1sen!2sma!4v1769193852266!5m2!1sen!2sma',
map_url: 'https://maps.app.goo.gl/pbSW4y4tt9RThx4a7'
},
agadir: {
label: 'Agadir',
map_iframe_src:
'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3441.2113225552594!2d-9.5471754!3d30.4017457!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xdb3b75d3674dc17%3A0x6d8f9adfd62e6d3d!2sGLS%20Sprachenzentrum%20-%20Centre%20GLS%20de%20langue%20Allemande%20Agadir!5e0!3m2!1sen!2sma!4v1769193780739!5m2!1sen!2sma',
map_url: 'https://maps.app.goo.gl/VX48ZDGFyXCyxsGU7'
}
};

// Get DOM elements
const mapFrame = document.getElementById('mapFrame');
const mapLink = document.getElementById('mapLink');
const mapContainer = mapLink;

// Carousel state
let currentIndex = 0;
let carouselInterval = null;
const CAROUSEL_INTERVAL = 4500; // 4.5 seconds

/**
* Update map display for given center key
*/
function updateMap(centerKey) {
const data = centerData[centerKey];
if (data && mapFrame && mapLink) {
mapFrame.src = data.map_iframe_src;
mapLink.href = data.map_url;
}
}

/**
* Move to next center in carousel
*/
function nextCenter() {
currentIndex = (currentIndex + 1) % centersInOrder.length;
updateMap(centersInOrder[currentIndex]);
}

/**
* Start auto-carousel
*/
function startCarousel() {
if (carouselInterval) clearInterval(carouselInterval);
carouselInterval = setInterval(nextCenter, CAROUSEL_INTERVAL);
}

/**
* Stop auto-carousel
*/
function stopCarousel() {
if (carouselInterval) {
clearInterval(carouselInterval);
carouselInterval = null;
}
}

// Initialize: start with first center (rabat)
updateMap(centersInOrder[currentIndex]);
startCarousel();

// Pause carousel on hover
if (mapContainer) {
mapContainer.addEventListener('mouseenter', stopCarousel);
mapContainer.addEventListener('mouseleave', startCarousel);
}
});
