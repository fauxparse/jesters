<?php
/**
 * Template Name: Homepage
 * @package WordPress
 * @subpackage Jesters
 */
?>

<?php get_header(); ?>

<div id="home">
  <div id="header">
    <a id="logo" href="/">The Court Jesters</a>
    <blockquote>
      <p>“One of the <strong>best</strong> improv troupes I’ve come across!”</p>
      <cite>Nancy Cartwright (The Simpsons)</cite>
    </blockquote>
  </div>

  <div id="navigator" class="span-24">
    <div id="main-photo" class="span-8">&nbsp;</div>
    <div id="tabs" class="span-16 last">
      <ul>
        <li class="active">
          <a class="primary-link" href="/about">Who we are</a>
          <ul class="secondary-links">
            <li><a href="/about/us">Meet the Jesters</a></li>
            <li><a href="/about/casting">Casting information</a></li>
            <li><a href="/about/theatre">The Court Theatre</a></li>
          </ul>
          <div class="details">
            <div class="teaser">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              <a class="more" href="/about">Read more »</a>
            </div>
          </div>
        </li>
        <li>
          <a class="primary-link" href="/products">What we do</a>
          <ul class="secondary-links">
            <li><a href="/products/improvisation">Improvisation</a></li>
            <li><a href="/products/entertainment">Corporate Entertainment</a></li>
            <li><a href="/products/blah">Blah blah</a></li>
          </ul>
          <div class="details">
            <div class="teaser" style="display: none">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              <a class="more" href="/products">Read more »</a>
            </div>
          </div>
        </li>
        <li>
          <a class="primary-link" href="/events">Your next event</a>
          <ul class="secondary-links">
            <li><a href="/events/packages">Packages</a></li>
            <li><a href="/events/entertainment">Characters</a></li>
            <li><a href="/events/mc">MCs</a></li>
          </ul>
          <div class="details">
            <div class="teaser" style="display: none">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              <a class="more" href="/events">Read more »</a>
            </div>
          </div>
        </li>
        <li>
          <a class="primary-link" href="/events">Training</a>
          <ul class="secondary-links">
            <li><a href="/training/corporate">Corporate training</a></li>
            <li><a href="/training/workshops">Workshops</a></li>
            <li><a href="/training/schools">Theatre Sports in schools</a></li>
          </ul>
          <div class="details">
            <div class="teaser" style="display: none">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              <a class="more" href="/events">Read more »</a>
            </div>
          </div>
        </li>
        <li>
          <a class="primary-link" href="/contact">Get in touch</a>
          <ul class="secondary-links">
            <li><a href="/contact">Contact details</a></li>
            <li><a href="http://www.facebook.com/group.php?gid=6407919733" target="_blank">Join us on Facebook</a></li>
            <li><a href="http://twitter.com/courtjesters" target="_blank">Follow us on Twitter</a></li>
          </ul>
          <div class="details">
            <div class="teaser" style="display: none">
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
              <a class="more" href="/contact">Read more »</a>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>

  <div id="whats-on" class="span-24">
    <div class="span-8">
      <h2>What's on?</h2>
    </div>
    <div class="span-16">
      
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    $('#navigator .primary-link').click(function() {
      var section = $(this).closest('li').not('.active');
      if (section.length > 0) {
        section.addClass('active').find('.teaser').show('blind');
        section.siblings('.active').removeClass('active').find('.teaser').hide('blind');
      }
      return false;
    });
  });
</script>

<?php get_footer(); ?>
