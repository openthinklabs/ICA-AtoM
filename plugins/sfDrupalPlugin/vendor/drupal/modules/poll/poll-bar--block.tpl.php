<?php
// $Id: poll-bar--block.tpl.php 6875 2010-05-13 00:31:07Z jablko $

/**
 * @file
 * Default theme implementation to display the bar for a single choice in a
 * poll.
 *
 * Variables available:
 * - $title: The title of the poll.
 * - $votes: The number of votes for this choice
 * - $total_votes: The number of votes for this choice
 * - $percentage: The percentage of votes for this choice.
 * - $vote: The choice number of the current user's vote.
 * - $voted: Set to TRUE if the user voted for this choice.
 *
 * @see template_preprocess_poll_bar()
 */
?>

<div class="text"><?php print $title; ?></div>
<div class="bar">
  <div style="width: <?php print $percentage; ?>%;" class="foreground"></div>
</div>
<div class="percent">
  <?php print $percentage; ?>%
</div>
