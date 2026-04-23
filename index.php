<?php
session_start();
?>
<!DOCTYPE html>
<html lang="uk">

<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/viewer.css" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/font-awesome.css" />
  <title>StarBook</title>
</head>

<body>
  <?php
  include('header.php');
  include('models/objectModel.php');
  $types = getTypes();
  ?>
  <div class="back"></div>
  <div class="slider hc">
    <div class="slider-btn left">&lt;
      </div>
        <div class="slider-content">
          <?php
          foreach ($types as $t) { ?>
            <div class="slider-item">
              <img class="slider-img" src="public/images/objects/<?= $t['image'] ?>">
              <div class="slider-t title"><?= $t['name'] ?></div>
              <div class="slider-t t"><?= $t['description'] ?></div>
            </div>
            <?php
          }
          ?>
        </div>
        <div class="slider-btn right">&gt;</div>
    </div>
  <script>
  const items = document.querySelectorAll('.slider-item');
  const leftBtn = document.querySelector('.slider-btn.left');
  const rightBtn = document.querySelector('.slider-btn.right');
  const slider = document.querySelector('.slider.hc');

  let index = 0;
  let interval;

  function updateSlider() {
    items.forEach((i, idx) => {
      i.classList.toggle('active', idx === index);
    });
  }

  function next() {
    index++;
    if (index >= items.length) index = 0;
    updateSlider();
  }

  function prev() {
    index--;
    if (index < 0) index = items.length - 1;
    updateSlider();
  }

  rightBtn.addEventListener('click', next);
  leftBtn.addEventListener('click', prev);

  function startAuto() {
    stopAuto();
    interval = setInterval(next, 5000);
  }

  function stopAuto() {
    if (interval) clearInterval(interval);
  }

  slider.addEventListener('mouseenter', stopAuto);
  slider.addEventListener('mouseleave', startAuto);

  updateSlider();
  startAuto();
</script>
</body>
</html>
