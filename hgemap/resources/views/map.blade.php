<!DOCTYPE html>
<html>
  <head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Hgees Map</title>
    <script>
      // Make initMap available globally before the API loads
      window.initMap = function() {
        // This will be replaced by the actual function after DOM loads
      };
    </script>
    <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&callback=initMap&libraries=maps,marker&v=beta">
    </script>
    <style>
      /* Always set the map height explicitly to define the size of the div
       * element that contains the map. */
      #map {
        height: 100%;
      }

      /* Optional: Makes the sample page fill the window. */
      html,
      body {
        height: 100%;
        margin: 0;
        padding: 0;
        font-family: Roboto, Arial, sans-serif;
      }

      /* Layout */
      .app-container {
        display: flex;
        height: 100vh;
      }

      .sidebar {
        width: 350px;
        background: white;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
        z-index: 1000;
      }

      .sidebar-header {
        display: flex;
        justify-content: center;
  
        padding: 20px;
        border-bottom: 1px solid #e0e0e0;
        background: #f8f9fa;
      }

      .sidebar-header h2 {
        margin-top: 30px;
        margin-right: 50px;
        margin-left: 20px;
        font-size: 18px;
        color: #333;
      }

      .sidebar-content {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
      }

      .map-container {
        flex: 1;
        position: relative;
      }

      /* Control Button Styles */
      .control-btn {
        background-color: #fff;
        border: 2px solid #fff;
        border-radius: 3px;
        box-shadow: 0 2px 6px rgba(0,0,0,.3);
        cursor: pointer;
        font-family: Roboto,Arial,sans-serif;
        font-size: 16px;
        line-height: 38px;
        margin: 8px 0 22px;
        padding: 0 12px;
        text-align: center;
        user-select: none;
        color: #1a73e8;
        font-weight: 500;
        transition: all 0.2s ease;
        margin-right: 8px;
      }

      .control-btn:hover {
        background-color: #f8f9fa;
        box-shadow: 0 4px 8px rgba(0,0,0,.3);
      }

      .control-btn.active {
        background-color: #1a73e8;
        color: white;
      }

      .control-btn.active:hover {
        background-color: #1557b0;
      }

      .rotate-btn {
        padding: 0 8px;
        font-size: 18px;
        line-height: 36px;
        margin-right: 4px;
      }

      .controls-container {
        display: flex;
        align-items: center;
      }

      /* Sidebar Styles */
      .marker-info {
        background: #f8f9fa;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
      }

      .marker-info h3 {
        margin: 0 0 10px 0;
        color: #333;
        font-size: 18px;
      }

      .marker-info p {
        margin: 0 0 15px 0;
        color: #666;
        line-height: 1.5;
      }

      .marker-coordinates {
        font-size: 12px;
        color: #888;
        margin-bottom: 15px;
      }

      .marker-actions {
        display: flex;
        gap: 10px;
      }

      .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
      }

      .btn-edit {
        background: #1a73e8;
        color: white;
      }

      .btn-edit:hover {
        background: #1557b0;
      }

      .btn-delete {
        background: #dc3545;
        color: white;
      }

      .btn-delete:hover {
        background: #c82333;
      }

      .btn-save {
        background: #28a745;
        color: white;
      }

      .btn-save:hover {
        background: #218838;
      }

      .btn-cancel {
        background: #6c757d;
        color: white;
      }

      .btn-cancel:hover {
        background: #5a6268;
      }

      /* Form Styles */
      .edit-form {
        display: none;
      }

      .form-group {
        margin-bottom: 15px;
      }

      .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
        color: #333;
      }

      .form-group input,
      .form-group textarea {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
        box-sizing: border-box;
      }

      .form-group textarea {
        resize: vertical;
        min-height: 80px;
      }

      .no-selection {
        text-align: center;
        color: #888;
        font-style: italic;
        margin-top: 50px;
      }

      .loading {
        text-align: center;
        color: #666;
        padding: 20px;
      }
    </style>
  </head>
  <body>
    <div class="app-container">
      <div class="sidebar">
        <div class="sidebar-header">
          <a href="">
            <img src="/images/logo1.png" alt="HGEELogo" style="width:100px;height:100px;">
          </a><h2>Holy Ghost Extension Map</h2>
        </div>
        <div class="sidebar-content">
          <div id="marker-details">
            <div class="no-selection">
              Click on a marker or polygon to view details
            </div>
          </div>
        </div>
      </div>
      <div class="map-container">
        <div id="map"></div>
      </div>
    </div>

    <script>
      let drawLineButton;
      let selectedLine = null;
      let isDrawingLineMode = false;
      let infoWindow; 
      let map, marker;
      let is3DMode = false;
      let currentRotation = 0;
      let polygon = null;
      let isPolygonVisible = true;
      let allPolylines = [];
      let allMarkers = []; // Store all marker instances
      let allPolygons = []; // Store all polygon instances
      let selectedMarker = null; // Track currently selected marker
      let selectedPolygon = null; // Track currently selected polygon
      let drawingManager;
      let isDrawingMode = false;

      // Parse the KML coordinates into Google Maps format
      const polygonCoordinates = [
        { lat: 16.4195639, lng: 120.6047595 },
        { lat: 16.4195066, lng: 120.6048307 },
        { lat: 16.4194899, lng: 120.6048454 },
        { lat: 16.4194638, lng: 120.6048178 },
        { lat: 16.4193644, lng: 120.6047318 },
        { lat: 16.4192973, lng: 120.6046739 },
        { lat: 16.4192359, lng: 120.6046207 },
        { lat: 16.4191571, lng: 120.6045527 },
        { lat: 16.4191302, lng: 120.6045831 },
        { lat: 16.4190353, lng: 120.6046901 },
        { lat: 16.4189928, lng: 120.6047032 },
        { lat: 16.4189698, lng: 120.6047017 },
        { lat: 16.4189656, lng: 120.6047013 },
        { lat: 16.4189339, lng: 120.6046988 },
        { lat: 16.4187941, lng: 120.6046896 },
        { lat: 16.4184532, lng: 120.6045911 },
        { lat: 16.4184484, lng: 120.6046039 },
        { lat: 16.4184446, lng: 120.6046138 },
        { lat: 16.4184316, lng: 120.6046483 },
        { lat: 16.4184045, lng: 120.6047199 },
        { lat: 16.4183809, lng: 120.6047792 },
        { lat: 16.4183717, lng: 120.6048021 },
        { lat: 16.4183323, lng: 120.6048736 },
        { lat: 16.4182641, lng: 120.6049748 },
        { lat: 16.4182539, lng: 120.60499 },
        { lat: 16.4182497, lng: 120.6049962 },
        { lat: 16.4182441, lng: 120.6050037 },
        { lat: 16.4182195, lng: 120.6050364 },
        { lat: 16.418151, lng: 120.6051279 },
        { lat: 16.4181305, lng: 120.6051119 },
        { lat: 16.4181209, lng: 120.6051016 },
        { lat: 16.4180787, lng: 120.6050566 },
        { lat: 16.4180434, lng: 120.6050137 },
        { lat: 16.4180291, lng: 120.6049152 },
        { lat: 16.4179456, lng: 120.6049157 },
        { lat: 16.4179417, lng: 120.6049817 },
        { lat: 16.4179033, lng: 120.6049687 },
        { lat: 16.417852, lng: 120.6049513 },
        { lat: 16.417781, lng: 120.604934 },
        { lat: 16.4176597, lng: 120.6049045 },
        { lat: 16.4176377, lng: 120.6048982 },
        { lat: 16.4175626, lng: 120.6048746 },
        { lat: 16.417493, lng: 120.6048527 },
        { lat: 16.4175463, lng: 120.6046756 },
        { lat: 16.4175753, lng: 120.6045784 },
        { lat: 16.4176011, lng: 120.6044919 },
        { lat: 16.4174664, lng: 120.6043726 },
        { lat: 16.4173723, lng: 120.604293 },
        { lat: 16.4172855, lng: 120.6042209 },
        { lat: 16.4171946, lng: 120.6041412 },
        { lat: 16.4171093, lng: 120.6040685 },
        { lat: 16.4170042, lng: 120.6039772 },
        { lat: 16.4168796, lng: 120.6038662 },
        { lat: 16.4167887, lng: 120.6037889 },
        { lat: 16.416627, lng: 120.6036459 },
        { lat: 16.4165339, lng: 120.6035672 },
        { lat: 16.4163382, lng: 120.6033957 },
        { lat: 16.4163315, lng: 120.6033889 },
        { lat: 16.4162977, lng: 120.6033607 },
        { lat: 16.4160704, lng: 120.6031631 },
        { lat: 16.4159446, lng: 120.6030487 },
        { lat: 16.4158385, lng: 120.6029622 },
        { lat: 16.4158156, lng: 120.6029423 },
        { lat: 16.4155826, lng: 120.6027368 },
        { lat: 16.4154107, lng: 120.6025628 },
        { lat: 16.4154531, lng: 120.6025502 },
        { lat: 16.4155207, lng: 120.6025307 },
        { lat: 16.4156367, lng: 120.6024973 },
        { lat: 16.4158972, lng: 120.6024218 },
        { lat: 16.4160977, lng: 120.6023637 },
        { lat: 16.4162926, lng: 120.6023072 },
        { lat: 16.4164094, lng: 120.6023937 },
        { lat: 16.4165338, lng: 120.602461 },
        { lat: 16.416641, lng: 120.6025201 },
        { lat: 16.4168397, lng: 120.6026274 },
        { lat: 16.4169082, lng: 120.6026575 },
        { lat: 16.4168928, lng: 120.6028032 },
        { lat: 16.4168803, lng: 120.6029274 },
        { lat: 16.4168633, lng: 120.6030828 },
        { lat: 16.4168603, lng: 120.6031252 },
        { lat: 16.4169336, lng: 120.6031347 },
        { lat: 16.4172924, lng: 120.6031562 },
        { lat: 16.4174866, lng: 120.6031563 },
        { lat: 16.4176055, lng: 120.6031564 },
        { lat: 16.417513, lng: 120.6030397 },
        { lat: 16.4174193, lng: 120.6029201 },
        { lat: 16.4173461, lng: 120.6028268 },
        { lat: 16.4173444, lng: 120.6028246 },
        { lat: 16.4172758, lng: 120.6027371 },
        { lat: 16.4172734, lng: 120.6027341 },
        { lat: 16.4172284, lng: 120.6026766 },
        { lat: 16.4172272, lng: 120.6026751 },
        { lat: 16.4172012, lng: 120.6026419 },
        { lat: 16.416986, lng: 120.6023674 },
        { lat: 16.4169503, lng: 120.6023284 },
        { lat: 16.4179615, lng: 120.6018696 },
        { lat: 16.4179942, lng: 120.601825 },
        { lat: 16.4180276, lng: 120.6017736 },
        { lat: 16.4180458, lng: 120.6017864 },
        { lat: 16.4180824, lng: 120.601804 },
        { lat: 16.4181571, lng: 120.6018651 },
        { lat: 16.4182501, lng: 120.6019232 },
        { lat: 16.4183567, lng: 120.6020543 },
        { lat: 16.4184344, lng: 120.6020442 },
        { lat: 16.4186284, lng: 120.6021261 },
        { lat: 16.4187086, lng: 120.6021601 },
        { lat: 16.4186963, lng: 120.6022005 },
        { lat: 16.4186913, lng: 120.602214 },
        { lat: 16.4186682, lng: 120.6022861 },
        { lat: 16.4186657, lng: 120.602294 },
        { lat: 16.4186251, lng: 120.6023985 },
        { lat: 16.418652, lng: 120.6024466 },
        { lat: 16.4186295, lng: 120.6026317 },
        { lat: 16.4186205, lng: 120.6027473 },
        { lat: 16.4186115, lng: 120.6028612 },
        { lat: 16.4186139, lng: 120.6030252 },
        { lat: 16.4187438, lng: 120.6029533 },
        { lat: 16.4189084, lng: 120.6029169 },
        { lat: 16.4190247, lng: 120.602891 },
        { lat: 16.4190645, lng: 120.6028826 },
        { lat: 16.4191329, lng: 120.6028682 },
        { lat: 16.4192228, lng: 120.6028494 },
        { lat: 16.4193011, lng: 120.6028376 },
        { lat: 16.4193413, lng: 120.6028316 },
        { lat: 16.4193693, lng: 120.6028275 },
        { lat: 16.4194782, lng: 120.6028112 },
        { lat: 16.4195196, lng: 120.6028043 },
        { lat: 16.4195567, lng: 120.6027982 },
        { lat: 16.4195642, lng: 120.6028431 },
        { lat: 16.4195801, lng: 120.6029385 },
        { lat: 16.4195988, lng: 120.6030005 },
        { lat: 16.4196335, lng: 120.6030755 },
        { lat: 16.4196842, lng: 120.6031321 },
        { lat: 16.4197104, lng: 120.6031565 },
        { lat: 16.4197332, lng: 120.6031695 },
        { lat: 16.4198074, lng: 120.6031981 },
        { lat: 16.4198342, lng: 120.603205 },
        { lat: 16.4198802, lng: 120.6032168 },
        { lat: 16.4200106, lng: 120.6032326 },
        { lat: 16.4201666, lng: 120.603261 },
        { lat: 16.4202728, lng: 120.6032686 },
        { lat: 16.4203473, lng: 120.6032763 },
        { lat: 16.420356, lng: 120.6032779 },
        { lat: 16.4204385, lng: 120.603293 },
        { lat: 16.4204962, lng: 120.6033285 },
        { lat: 16.4205342, lng: 120.6033944 },
        { lat: 16.420586, lng: 120.6035643 },
        { lat: 16.4206083, lng: 120.6036375 },
        { lat: 16.4206122, lng: 120.60365 },
        { lat: 16.4206221, lng: 120.6037339 },
        { lat: 16.420606, lng: 120.6037944 },
        { lat: 16.4205336, lng: 120.603991 },
        { lat: 16.4204224, lng: 120.6042329 },
        { lat: 16.4203643, lng: 120.6043288 },
        { lat: 16.4203061, lng: 120.604386 },
        { lat: 16.4201383, lng: 120.6044671 },
        { lat: 16.420018, lng: 120.6045076 },
        { lat: 16.4198723, lng: 120.6045281 },
        { lat: 16.4197168, lng: 120.6045302 },
        { lat: 16.4196763, lng: 120.604542 },
        { lat: 16.4196164, lng: 120.6045785 },
        { lat: 16.4195777, lng: 120.6046379 },
        { lat: 16.4195595, lng: 120.6046852 },
        { lat: 16.4195569, lng: 120.6047303 },
        { lat: 16.4195639, lng: 120.6047595 }
      ];

      async function initMap() {
  const position = { lat: 16.41773712495209, lng: 120.60434706458075};

  // Request needed libraries
  const { Map } = await google.maps.importLibrary("maps");
  const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
  const { DrawingManager } = await google.maps.importLibrary("drawing");

  // Initialize the map
  map = new Map(document.getElementById("map"), {
    zoom: 17,
    center: position,
    mapId: "c171e8e673d6bd9a4152fe7a",
    tilt: 0,
    heading: 0,
    mapTypeControl: true,
    mapTypeControlOptions: {
      style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR,
      position: google.maps.ControlPosition.TOP_CENTER,
    },
    zoomControl: true,
    zoomControlOptions: {
      position: google.maps.ControlPosition.RIGHT_CENTER
    },
    streetViewControl: true,
    streetViewControlOptions: {
      position: google.maps.ControlPosition.RIGHT_CENTER
    },
    fullscreenControl: true,
  });
map.setMapTypeId(google.maps.MapTypeId.HYBRID);

  // Create a reusable InfoWindow
  infoWindow = new google.maps.InfoWindow();

  // Create the marker
  marker = new AdvancedMarkerElement({
    map: map,
    position: position,
    title: "HGEES",
  });

  // Create the original polygon
  createPolygon();

  // Initialize drawing manager
  initDrawingManager();

  // Create focus button
const focusButton = document.createElement("button");
focusButton.textContent = "Focus";
focusButton.classList.add("control-btn");
focusButton.title = "Go to initial position";

const drawLineButton = document.createElement("button");
drawLineButton.id = "draw-line-button";
drawLineButton.textContent = "Draw Line";
drawLineButton.classList.add("control-btn");
drawLineButton.title = "Draw a polyline";


  // Create controls container
  const controlsContainer = document.createElement("div");
  controlsContainer.classList.add("controls-container");

  // Create drawing toggle button
  const toggleDrawingButton = document.createElement("button");
  toggleDrawingButton.textContent = "Draw Polygon";
  toggleDrawingButton.classList.add("control-btn");
  toggleDrawingButton.title = "Toggle polygon drawing";

  // Create 3D toggle button
  const toggle3DButton = document.createElement("button");
  toggle3DButton.textContent = "3D";
  toggle3DButton.classList.add("control-btn");
  toggle3DButton.title = "Toggle 3D view";

  // Create rotate left button
  const rotateLeftButton = document.createElement("button");
  rotateLeftButton.innerHTML = "↶";
  rotateLeftButton.classList.add("control-btn", "rotate-btn");
  rotateLeftButton.title = "Rotate left";

  // Create rotate right button
  const rotateRightButton = document.createElement("button");
  rotateRightButton.innerHTML = "↷";
  rotateRightButton.classList.add("control-btn", "rotate-btn");
  rotateRightButton.title = "Rotate right";

  // Create polygon toggle button
  const togglePolygonButton = document.createElement("button");
  togglePolygonButton.textContent = "Hide Boundaries";
  togglePolygonButton.classList.add("control-btn", "active");
  togglePolygonButton.title = "Toggle area boundaries";

  drawLineButton.addEventListener("click", () => {
  toggleLineDrawingMode(drawLineButton);
});

  focusButton.addEventListener("click", () => {
  focusMap();
});
  // Add click events
  toggleDrawingButton.addEventListener("click", () => {
    toggleDrawingMode(toggleDrawingButton);
  });

  toggle3DButton.addEventListener("click", () => {
    toggle3DMode(toggle3DButton);
  });

  rotateLeftButton.addEventListener("click", () => {
    rotateMap(-45);
  });

  rotateRightButton.addEventListener("click", () => {
    rotateMap(45);
  });

  togglePolygonButton.addEventListener("click", () => {
    togglePolygon(togglePolygonButton);
  });

  // Add buttons to container
  controlsContainer.appendChild(toggleDrawingButton);
  controlsContainer.appendChild(rotateLeftButton);
  controlsContainer.appendChild(rotateRightButton);
  controlsContainer.appendChild(toggle3DButton);
  controlsContainer.appendChild(togglePolygonButton);
  controlsContainer.appendChild(focusButton);
  controlsContainer.appendChild(drawLineButton);

  // Add the container to the map
  map.controls[google.maps.ControlPosition.RIGHT_TOP].push(controlsContainer);

  // When the map is clicked (and not in drawing mode), prompt the user for marker info.
  map.addListener("click", (e) => {
    if (isDrawingMode) return; // Don't create markers while drawing

    const lat = e.latLng.lat();
    const lng = e.latLng.lng();

    const title = prompt("Enter marker title:");
    if (!title) return;

    const description = prompt("Enter marker description:");

    // Save the marker to your backend.
    fetch("/markers", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Accept": "application/json",
        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
      },
      body: JSON.stringify({ title, description, lat, lng })
    })
    .then(res => res.json())
    .then(markerData => {
      createMarkerOnMap(markerData);
    })
    .catch(error => {
      console.error('Error creating marker:', error);
      alert('Error creating marker. Please try again.');
    });
  });

  // Fetch and display existing markers and polygons
  loadExistingMarkers();
  loadExistingPolygons();
  loadExistingLines();
}

function initDrawingManager() {
  drawingManager = new google.maps.drawing.DrawingManager({
    drawingMode: null,
    drawingControl: false,
    polygonOptions: {
      strokeColor: '#FF0000',
      strokeOpacity: 0.8,
      strokeWeight: 4,
      fillColor: '#FF0000',
      fillOpacity: 0.35,
      editable: false,
      draggable: false
    }
  });

  drawingManager.setMap(map);

  // Listen for polygon completion
  google.maps.event.addListener(drawingManager, 'polygoncomplete', function(polygon) {
    // Stop drawing mode
    drawingManager.setDrawingMode(null);
    // Reset the button state
  const drawPolygonButton = document.querySelector('.control-btn.active');
  if (drawPolygonButton && drawPolygonButton.textContent === "Stop Drawing") {
    drawPolygonButton.classList.remove("active");
    drawPolygonButton.textContent = "Draw Polygon";
    drawPolygonButton.title = "Start polygon drawing";
    isDrawingMode = false;
  }
    // Get coordinates
    const coordinates = polygon.getPath().getArray().map(coord => ({
      lat: coord.lat(),
      lng: coord.lng()
    }));

    // Prompt for title and description
    const title = prompt("Enter polygon title:");
    if (!title) {
      polygon.setMap(null);
      return;
    }

    const description = prompt("Enter polygon description:");

    // Save polygon to backend
    savePolygonToBackend(polygon, title, description, coordinates);
  });

  google.maps.event.addListener(drawingManager, 'polylinecomplete', function(polyline) {
  drawingManager.setDrawingMode(null);
// Reset the draw line button UI state
  const lineBtn = document.getElementById("draw-line-button");
  if (lineBtn) {
    lineBtn.classList.remove("active");
    lineBtn.textContent = "Draw Line";
    lineBtn.title = "Draw a polyline";
  }

  isDrawingLineMode = false;

  
  const coordinates = polyline.getPath().getArray().map(coord => ({
    lat: coord.lat(),
    lng: coord.lng()
  }));

  const title = prompt("Enter line title:");
  if (!title) {
    polyline.setMap(null);
    return;
  }
  const description = prompt("Enter line description:");

  saveLineToBackend(polyline, title, description, coordinates);
});

}
function saveLineToBackend(polyline, title, description, coordinates) {
  fetch("/lines", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ 
      title, 
      description, 
      coordinates,
      stroke_color: '#0000FF',
      stroke_opacity: 1.0,
      stroke_weight: 2,
    })
  })
  .then(res => res.json())
  .then(lineData => {
    polyline.setMap(null);
    createLineOnMap(lineData);
  })
  .catch(error => {
    console.error('Error creating line:', error);
    alert('Error creating line. Please try again.');
    polyline.setMap(null);
  });
}


function savePolygonToBackend(polygonShape, title, description, coordinates) {
  fetch("/polygons", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      "Accept": "application/json",
      "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ 
      title, 
      description, 
      coordinates,
      stroke_color: '#FF0000',
      stroke_opacity: 0.8,
      stroke_weight: 2,
      fill_color: '#FF0000',
      fill_opacity: 0.35
    })
  })
  .then(res => res.json())
  .then(polygonData => {
  // Only remove the temporary polygon if the backend returns success
  if (polygonData && polygonData.coordinates) {
    polygonShape.setMap(null); // remove the temp one
    createPolygonOnMap(polygonData); // show saved polygon
  } else {
    console.error('Invalid polygon data returned:', polygonData);
    alert('Failed to save polygon. Please try again.');
  }
})
}
function loadExistingLines() {
  fetch("/lines")
    .then(res => res.json())
    .then(lines => {
      lines.forEach(lineData => {
        createLineOnMap(lineData);
        
      });
    })
    .catch(error => {
      console.error('Error loading lines:', error);
    });
}

function createLineOnMap(lineData) {
  const polyline = new google.maps.Polyline({
    path: lineData.coordinates,
    strokeColor: lineData.stroke_color,
    strokeOpacity: lineData.stroke_opacity,
    strokeWeight: lineData.stroke_weight,
    clickable: true,
    map: map
  });

  polyline.lineData = lineData;
  allPolylines.push(polyline);

  polyline.addListener("click", () => {
    showLineDetails(lineData, polyline);
  });
}


function createPolygonOnMap(polygonData) {
  const polygonShape = new google.maps.Polygon({
    paths: polygonData.coordinates,
    strokeColor: polygonData.stroke_color,
    strokeOpacity: polygonData.stroke_opacity,
    strokeWeight: polygonData.stroke_weight,
    fillColor: polygonData.fill_color,
    fillOpacity: polygonData.fill_opacity,
    clickable: true
  });

  polygonShape.setMap(map);
  
  // Store polygon data
  polygonShape.polygonData = polygonData;
  allPolygons.push(polygonShape);

  // Add click event to show polygon details in sidebar
  polygonShape.addListener("click", () => {
    showPolygonDetails(polygonData, polygonShape);
  });
}

function loadExistingPolygons() {
  fetch("/polygons")
    .then(res => res.json())
    .then(polygons => {
      polygons.forEach(polygonData => {
        createPolygonOnMap(polygonData);
      });
    })
    .catch(error => {
      console.error('Error loading polygons:', error);
    });
}
function showLineDetails(lineData, polylineElement) {
  selectedLine = polylineElement;
  selectedMarker = null;
  selectedPolygon = null;

  const detailsContainer = document.getElementById('marker-details');

  detailsContainer.innerHTML = `
    <div class="marker-info">
      <div id="view-mode">
        <h3>${lineData.title}</h3>
        <p>${lineData.description || 'No description'}</p>
        <div class="marker-coordinates">
          Points: ${lineData.coordinates.length}
        </div>
        <div class="marker-actions">
          <button class="btn btn-edit" onclick="editLine(${lineData.id})">Edit</button>
          <button class="btn btn-delete" onclick="deleteLine(${lineData.id})">Delete</button>
        </div>
      </div>
      
      <div id="edit-mode" class="edit-form">
        <div class="form-group">
          <label for="edit-title">Title:</label>
          <input type="text" id="edit-title" value="${lineData.title}">
        </div>
        <div class="form-group">
          <label for="edit-description">Description:</label>
          <textarea id="edit-description">${lineData.description || ''}</textarea>
        </div>
        <div class="form-group">
          <label for="edit-stroke-color">Stroke Color:</label>
          <input type="color" id="edit-stroke-color" value="${lineData.stroke_color}">
        </div>
        <div class="form-group">
          <label for="edit-stroke-weight">Stroke Weight:</label>
          <input type="number" id="edit-stroke-weight" value="${lineData.stroke_weight}" min="1" max="10">
        </div>
        <div class="form-group">
          <label for="edit-stroke-opacity">Stroke Opacity:</label>
          <input type="range" id="edit-stroke-opacity" min="0" max="1" step="0.1" value="${lineData.stroke_opacity}">
          <span id="stroke-opacity-value">${lineData.stroke_opacity}</span>
        </div>
        <div class="marker-actions">
          <button class="btn btn-save" onclick="saveLine(${lineData.id})">Save</button>
          <button class="btn btn-cancel" onclick="cancelEdit()">Cancel</button>
        </div>
      </div>
    </div>
  `;

  const opacitySlider = document.getElementById('edit-stroke-opacity');
  const opacityValue = document.getElementById('stroke-opacity-value');
  opacitySlider.addEventListener('input', function () {
    opacityValue.textContent = this.value;
  });
}


function showPolygonDetails(polygonData, polygonElement) {
  selectedPolygon = polygonElement;
  selectedMarker = null; // Clear marker selection
  const detailsContainer = document.getElementById('marker-details');
  
  detailsContainer.innerHTML = `
    <div class="marker-info">
      <div id="view-mode">
        <h3>${polygonData.title}</h3>
        <p>${polygonData.description || 'No description'}</p>
        <div class="marker-coordinates">
          Points: ${polygonData.coordinates.length}
        </div>
        <div class="marker-actions">
          <button class="btn btn-edit" onclick="editPolygon(${polygonData.id})">Edit</button>
          <button class="btn btn-delete" onclick="deletePolygon(${polygonData.id})">Delete</button>
        </div>
      </div>
      
      <div id="edit-mode" class="edit-form">
        <div class="form-group">
          <label for="edit-title">Title:</label>
          <input type="text" id="edit-title" value="${polygonData.title}">
        </div>
        <div class="form-group">
          <label for="edit-description">Description:</label>
          <textarea id="edit-description">${polygonData.description || ''}</textarea>
        </div>
        <div class="form-group">
          <label for="edit-stroke-color">Stroke Color:</label>
          <input type="color" id="edit-stroke-color" value="${polygonData.stroke_color}">
        </div>
        <div class="form-group">
          <label for="edit-fill-color">Fill Color:</label>
          <input type="color" id="edit-fill-color" value="${polygonData.fill_color}">
        </div>
        <div class="form-group">
          <label for="edit-fill-opacity">Fill Opacity:</label>
          <input type="range" id="edit-fill-opacity" min="0" max="1" step="0.1" value="${polygonData.fill_opacity}">
          <span id="opacity-value">${polygonData.fill_opacity}</span>
        </div>
        <div class="marker-actions">
          <button class="btn btn-save" onclick="savePolygon(${polygonData.id})">Save</button>
          <button class="btn btn-cancel" onclick="cancelEdit()">Cancel</button>
        </div>
      </div>
    </div>
  `;

  // Add opacity slider listener
  const opacitySlider = document.getElementById('edit-fill-opacity');
  const opacityValue = document.getElementById('opacity-value');
  if (opacitySlider && opacityValue) {
    opacitySlider.addEventListener('input', function() {
      opacityValue.textContent = this.value;
    });
  }
}
function editLine(lineId) {
  document.getElementById('view-mode').style.display = 'none';
  document.getElementById('edit-mode').style.display = 'block';
}

function cancelEdit() {
  document.getElementById('view-mode').style.display = 'block';
  document.getElementById('edit-mode').style.display = 'none';
}


function editPolygon(polygonId) {
  document.getElementById('view-mode').style.display = 'none';
  document.getElementById('edit-mode').style.display = 'block';
}

function savePolygon(polygonId) {
  const title = document.getElementById('edit-title').value;
  const description = document.getElementById('edit-description').value;
  const strokeColor = document.getElementById('edit-stroke-color').value;
  const fillColor = document.getElementById('edit-fill-color').value;
  const fillOpacity = parseFloat(document.getElementById('edit-fill-opacity').value);

  if (!title.trim()) {
    alert('Title is required');
    return;
  }

  const detailsContainer = document.getElementById('marker-details');
  detailsContainer.innerHTML = '<div class="loading">Saving...</div>';

  // Get current coordinates from the selected polygon
  const coordinates = selectedPolygon.polygonData.coordinates;

  fetch(`/polygons/${polygonId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ 
      title, 
      description, 
      coordinates,
      stroke_color: strokeColor,
      stroke_opacity: 0.8,
      stroke_weight: 2,
      fill_color: fillColor,
      fill_opacity: fillOpacity
    })
  })
  .then(res => res.json())
  .then(updatedPolygonData => {
    // Update the polygon on the map
    if (selectedPolygon) {
      selectedPolygon.setOptions({
        strokeColor: updatedPolygonData.stroke_color,
        fillColor: updatedPolygonData.fill_color,
        fillOpacity: updatedPolygonData.fill_opacity
      });
      selectedPolygon.polygonData = updatedPolygonData;
    }

    // Show updated details
    showPolygonDetails(updatedPolygonData, selectedPolygon);
    
    alert('Polygon updated successfully!');
  })
  .catch(error => {
    console.error('Error updating polygon:', error);
    alert('Error updating polygon. Please try again.');
    
    if (selectedPolygon && selectedPolygon.polygonData) {
      showPolygonDetails(selectedPolygon.polygonData, selectedPolygon);
    }
  });
}
function saveLine(lineId) {
  const title = document.getElementById('edit-title').value;
  const description = document.getElementById('edit-description').value;
  const strokeColor = document.getElementById('edit-stroke-color').value;
  const strokeWeight = parseInt(document.getElementById('edit-stroke-weight').value);
  const strokeOpacity = parseFloat(document.getElementById('edit-stroke-opacity').value);

  if (!title.trim()) {
    alert('Title is required');
    return;
  }

  const detailsContainer = document.getElementById('marker-details');
  detailsContainer.innerHTML = '<div class="loading">Saving...</div>';

  const coordinates = selectedLine.lineData.coordinates;

  fetch(`/lines/${lineId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
      title,
      description,
      coordinates,
      stroke_color: strokeColor,
      stroke_weight: strokeWeight,
      stroke_opacity: strokeOpacity
    })
  })
  .then(res => res.json())
  .then(updatedLine => {
    if (selectedLine) {
      selectedLine.setOptions({
        strokeColor: updatedLine.stroke_color,
        strokeWeight: updatedLine.stroke_weight,
        strokeOpacity: updatedLine.stroke_opacity
      });
      selectedLine.lineData = updatedLine;
    }
    showLineDetails(updatedLine, selectedLine);
    alert('Line updated successfully!');
  })
  .catch(error => {
    console.error('Error updating line:', error);
    alert('Error updating line. Please try again.');
    showLineDetails(selectedLine.lineData, selectedLine);
  });
}
function deleteLine(lineId) {
  if (!confirm('Are you sure you want to delete this line?')) {
    return;
  }

  const detailsContainer = document.getElementById('marker-details');
  detailsContainer.innerHTML = '<div class="loading">Deleting...</div>';

  fetch(`/lines/${lineId}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(res => res.json())
  .then(() => {
    if (selectedLine) {
      selectedLine.setMap(null);
      const index = allPolylines.indexOf(selectedLine);
      if (index > -1) allPolylines.splice(index, 1);
    }
    selectedLine = null;
    detailsContainer.innerHTML = '<div class="no-selection">Click on a marker or line to view details</div>';
    alert('Line deleted successfully!');
  })
  .catch(error => {
    console.error('Error deleting line:', error);
    alert('Error deleting line. Please try again.');
    if (selectedLine && selectedLine.lineData) {
      showLineDetails(selectedLine.lineData, selectedLine);
    }
  });
}

function deletePolygon(polygonId) {
  if (!confirm('Are you sure you want to delete this polygon?')) {
    return;
  }

  const detailsContainer = document.getElementById('marker-details');
  detailsContainer.innerHTML = '<div class="loading">Deleting...</div>';

  fetch(`/polygons/${polygonId}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(res => res.json())
  .then(() => {
    // Remove polygon from map
    if (selectedPolygon) {
      selectedPolygon.setMap(null);
      
      // Remove from allPolygons array
      const index = allPolygons.indexOf(selectedPolygon);
      if (index > -1) {
        allPolygons.splice(index, 1);
      }
    }

    // Clear sidebar
    detailsContainer.innerHTML = '<div class="no-selection">Click on a marker or polygon to view details</div>';
    selectedPolygon = null;
    
    alert('Polygon deleted successfully!');
  })
  .catch(error => {
    console.error('Error deleting polygon:', error);
    alert('Error deleting polygon. Please try again.');
    
    if (selectedPolygon && selectedPolygon.polygonData) {
      showPolygonDetails(selectedPolygon.polygonData, selectedPolygon);
    }
  });
}
function toggleLineDrawingMode(button) {
  if (!isDrawingLineMode) {
    drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYLINE);
    button.classList.add("active");
    button.textContent = "Stop Drawing Line";
    isDrawingLineMode = true;
  } else {
    drawingManager.setDrawingMode(null);
    button.classList.remove("active");
    button.textContent = "Draw Line";
    isDrawingLineMode = false;
  }
}


function toggleDrawingMode(button) {
  if (!isDrawingMode) {
    // Enable drawing mode
    drawingManager.setDrawingMode(google.maps.drawing.OverlayType.POLYGON);
    button.classList.add("active");
    button.textContent = "Stop Drawing";
    button.title = "Stop polygon drawing";
    isDrawingMode = true;
  } else {
    // Disable drawing mode
    drawingManager.setDrawingMode(null);
    button.classList.remove("active");
    button.textContent = "Draw Polygon";
    button.title = "Start polygon drawing";
    isDrawingMode = false;
  }
}

// Keep all your existing functions (createMarkerOnMap, loadExistingMarkers, showMarkerDetails, 
// editMarker, cancelEdit, saveMarker, deleteMarker, createPolygon, togglePolygon, 
// toggle3DMode, rotateMap) exactly as they are

function createMarkerOnMap(markerData) {
  const markerPos = { 
    lat: parseFloat(markerData.lat), 
    lng: parseFloat(markerData.lng) 
  };
  
  const markerElement = new google.maps.marker.AdvancedMarkerElement({
    map: map,
    position: markerPos,
    title: markerData.title,
  });

  // Store marker data
  markerElement.markerData = markerData;
  allMarkers.push(markerElement);

  // Add click event to show marker details in sidebar
  markerElement.addListener("click", () => {
    showMarkerDetails(markerData, markerElement);
  });
}

function loadExistingMarkers() {
  fetch("/markers")
    .then(res => res.json())
    .then(markers => {
      markers.forEach(markerData => {
        createMarkerOnMap(markerData);
      });
    })
    .catch(error => {
      console.error('Error loading markers:', error);
    });
}

function showMarkerDetails(markerData, markerElement) {
  selectedMarker = markerElement;
  selectedPolygon = null; // Clear polygon selection
  const detailsContainer = document.getElementById('marker-details');
  
  detailsContainer.innerHTML = `
    <div class="marker-info">
      <div id="view-mode">
        <h3>${markerData.title}</h3>
        <p>${markerData.description || 'No description'}</p>
        <div class="marker-coordinates">
          Lat: ${parseFloat(markerData.lat).toFixed(6)}, Lng: ${parseFloat(markerData.lng).toFixed(6)}
        </div>
        <div class="marker-actions">
          <button class="btn btn-edit" onclick="editMarker(${markerData.id})">Edit</button>
          <button class="btn btn-delete" onclick="deleteMarker(${markerData.id})">Delete</button>
        </div>
      </div>
      
      <div id="edit-mode" class="edit-form">
        <div class="form-group">
          <label for="edit-title">Title:</label>
          <input type="text" id="edit-title" value="${markerData.title}">
        </div>
        <div class="form-group">
          <label for="edit-description">Description:</label>
          <textarea id="edit-description">${markerData.description || ''}</textarea>
        </div>
        <div class="form-group">
          <label for="edit-lat">Latitude:</label>
          <input type="number" id="edit-lat" value="${markerData.lat}" step="0.000001">
        </div>
        <div class="form-group">
          <label for="edit-lng">Longitude:</label>
          <input type="number" id="edit-lng" value="${markerData.lng}" step="0.000001">
        </div>
        <div class="marker-actions">
          <button class="btn btn-save" onclick="saveMarker(${markerData.id})">Save</button>
          <button class="btn btn-cancel" onclick="cancelEdit()">Cancel</button>
        </div>
      </div>
    </div>
  `;
}

function editMarker(markerId) {
  document.getElementById('view-mode').style.display = 'none';
  document.getElementById('edit-mode').style.display = 'block';
}

function cancelEdit() {
  document.getElementById('view-mode').style.display = 'block';
  document.getElementById('edit-mode').style.display = 'none';
}

function saveMarker(markerId) {
  const title = document.getElementById('edit-title').value;
  const description = document.getElementById('edit-description').value;
  const lat = parseFloat(document.getElementById('edit-lat').value);
  const lng = parseFloat(document.getElementById('edit-lng').value);

  if (!title.trim()) {
    alert('Title is required');
    return;
  }

  if (isNaN(lat) || isNaN(lng)) {
    alert('Please enter valid coordinates');
    return;
  }

  const detailsContainer = document.getElementById('marker-details');
  detailsContainer.innerHTML = '<div class="loading">Saving...</div>';

  fetch(`/markers/${markerId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({ title, description, lat, lng })
  })
  .then(res => res.json())
  .then(updatedMarkerData => {
    // Update the marker on the map
    if (selectedMarker) {
      const newPosition = { lat: updatedMarkerData.lat, lng: updatedMarkerData.lng };
      selectedMarker.position = newPosition;
      selectedMarker.title = updatedMarkerData.title;
      selectedMarker.markerData = updatedMarkerData;
    }

    // Show updated details
    showMarkerDetails(updatedMarkerData, selectedMarker);
    
    alert('Marker updated successfully!');
  })
  .catch(error => {
    console.error('Error updating marker:', error);
    alert('Error updating marker. Please try again.');
    
    // Show the marker details again on error
    if (selectedMarker && selectedMarker.markerData) {
      showMarkerDetails(selectedMarker.markerData, selectedMarker);
    }
  });
}

function deleteMarker(markerId) {
  if (!confirm('Are you sure you want to delete this marker?')) {
    return;
  }

  const detailsContainer = document.getElementById('marker-details');
  detailsContainer.innerHTML = '<div class="loading">Deleting...</div>';

  fetch(`/markers/${markerId}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
  })
  .then(res => res.json())
  .then(() => {
    // Remove marker from map
    if (selectedMarker) {
      selectedMarker.map = null;
      
      // Remove from allMarkers array
      const index = allMarkers.indexOf(selectedMarker);
      if (index > -1) {
        allMarkers.splice(index, 1);
      }
    }
          // Clear sidebar
          detailsContainer.innerHTML = '<div class="no-selection">Click on a marker to view details</div>';
          selectedMarker = null;
          
          alert('Marker deleted successfully!');
        })
        .catch(error => {
          console.error('Error deleting marker:', error);
          alert('Error deleting marker. Please try again.');
          
          // Show the marker details again on error
          if (selectedMarker && selectedMarker.markerData) {
            showMarkerDetails(selectedMarker.markerData, selectedMarker);
          }
        });
      }

      function createPolygon() {
        // Create a polygon with the coordinates
        polygon = new google.maps.Polygon({
          paths: polygonCoordinates,
          strokeColor: "#FF0000",
          strokeOpacity: 0.8,
          strokeWeight: 3,
          fillColor: "#FF0000",
          fillOpacity: 0,
          clickable: false
        });

        // Set the polygon on the map
        polygon.setMap(map);

        // Add event listener for polygon clicks
        polygon.addListener('click', function(event) {
          console.log('Polygon clicked at:', event.latLng.toString());
          // You can add custom logic here when the polygon is clicked
        });
      }

      function togglePolygon(button) {
        if (isPolygonVisible) {
          // Hide polygon
          polygon.setMap(null);
          button.classList.remove("active");
          button.textContent = "Show Boundaries";
          button.title = "Show area boundaries";
          isPolygonVisible = false;
        } else {
          // Show polygon
          polygon.setMap(map);
          button.classList.add("active");
          button.textContent = "Hide Boundaries";
          button.title = "Hide area boundaries";
          isPolygonVisible = true;
        }
      }

      function toggle3DMode(button) {
        if (!is3DMode) {
          // Enable 3D mode
          map.setTilt(45);
          button.classList.add("active");
          button.textContent = "2D";
          button.title = "Switch to 2D view";
          is3DMode = true;
        } else {
          // Disable 3D mode
          map.setTilt(0);
          map.setHeading(0);
          currentRotation = 0;
          button.classList.remove("active");
          button.textContent = "3D";
          button.title = "Toggle 3D view";
          is3DMode = false;
        }
      }

      function rotateMap(degrees) {
        // Only allow rotation in 3D mode
        if (!is3DMode) {
          return;
        }
        
        currentRotation += degrees;
        // Keep rotation between 0-360 degrees
        currentRotation = ((currentRotation % 360) + 360) % 360;
        map.setHeading(currentRotation);
      }

      function focusMap() {
  const initialPosition = { lat: 16.41773712495209, lng: 120.60434706458075 };
  map.setCenter(initialPosition);
  map.setZoom(17);
}


      // Override the global initMap with the actual function
      window.initMap = initMap;
      
      // If the API has already loaded, call initMap immediately
      if (typeof google !== 'undefined' && google.maps) {
        initMap();
      }
      
    </script>
  </body>
</html>