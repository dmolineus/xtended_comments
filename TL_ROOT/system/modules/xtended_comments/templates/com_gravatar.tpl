
<div class="comment_default<?php echo $this->class; ?>" id="<?php echo $this->id; ?>">
<p class="info"><?php echo $this->by; ?> <?php if ($this->website): ?><a href="<?php echo $this->website; ?>" rel="nofollow"<?php echo LINK_NEW_WINDOW; ?>><?php endif; echo $this->name; ?><?php if ($this->website): ?></a><?php endif; ?><span class="date"> | <?php echo $this->date; ?></span></p>
<?php if($this->showAvatar):?><img src="<?php echo $this->avatarUrl; ?>" alt="<?php echo $this->name; ?>" class="avatar" <?php if($this->avatarSize > 0): ?>width="<?php echo $this->avatarSize; ?>"<?php endif; ?> /><?php endif; ?>
<div class="comment">
<?php echo $this->comment; ?> 
</div>
<div class="clear"></div>
</div>
