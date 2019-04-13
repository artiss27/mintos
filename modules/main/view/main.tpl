<h1 class="text-center">RSS LISTS</h1>
<?php
if (\User::$id) {
    foreach ($arResult as $k => $v) {
        echo '<div class="feed-list"><h2>' . hc($v['lists']['title']) . '</h2>';
        if (!empty($v['error'])) {
            echo '<div class="alert alert-danger">' . hc($v['error']) . '</div>';
        } elseif (!empty($v['lists'])) {
            $mFreqWorlds = $rss->getMostFrequentWorlds($k);
            $str = '';
            $i = 1;
            foreach ($mFreqWorlds as $world => $cnt) {
                if ($i > 10) break;
                $str .= '<span class="f-teg">' . hc($world) . ' (' . (int)$cnt . ')' . '</span>';
                $i++;
            }
            echo '<div class="feed-tegs alert alert-primary">' . $str . '</div>';
            foreach ($v['lists'] as $k2 => $v2) {
              if (!is_int($k2)) continue; ?>
              <div class="feed-item">
                <h3 class="title"><a href="<?= hc($v2['link'] ?? ''); ?>"><?= hcE($v2['title'] ?? ''); ?></a></h3>
                <div class="author"><?= hc($v2['author'] ?? ''); ?> <span
                      class="date"><?= date("F j, Y, g:i a", strtotime($v2['date'] ?? '')); ?></span></div>
                <div class="description"><?= ($v2['description'] ?? ''); ?></div>
              </div>
            <?php }
        } else {
            echo '<div class="alert alert-danger">no feeds to display!</div>';
        }
        echo '</div>';
    }
} else { ?>
  <br>
  <h3 class="alert alert-danger text-center">Please login to see content.</h3>
<?php }

