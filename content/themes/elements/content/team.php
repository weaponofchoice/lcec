<section class="section_team">
  <div class="section_body">
    <?php
    $members = get_field( 'team_members' );
    
    if( $members ):
      foreach( $members as $member ):
        $member_picture = $member['team_single_picture'];
        $member_name = $member['team_single_name'];
        $member_role = $member['team_single_role'];
        $member_email = $member['team_single_email'];
        $member_link = $member['team_single_link'];
        $member_link_text = $member['team_single_link_text'];
        ?>
        
        <div>
          <div style="background-image:url('<?php echo $member_picture['sizes']['medium']; ?>');"></div>
          
          <div>
            <h3><?php echo $member_name; ?></h3>
            <p class="margin-b20"><?php echo $member_role; ?></p>
            <p><?php echo $member_email; ?></p>
            <a class="is_bold" href="<?php echo $member_link; ?>" target="_blank">&#8594; <?php echo $member_link_text; ?></a>
          </div>
        </div>
        
        <?php
      endforeach;
    endif;
    ?>
  </div>
</section>