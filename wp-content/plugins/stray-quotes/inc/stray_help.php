<?php function stray_help() {
	
	$widgetpage = get_option('siteurl')."/wp-admin/widgets.php";
	$toolspage = get_option('siteurl')."/wp-admin/admin.php?page=stray_tools";
	$options = get_option('siteurl')."/wp-admin/admin.php?page=stray_quotes_options"; 
	$manage = get_option('siteurl')."/wp-admin/admin.php?page=stray_manage"; ?>
    
    <div class="wrap" style="width:52%"><h2><?php _e('The help page','stray-quotes');?></h2>
       
        <?php // how do I display random quotes on this blog? ?>
        <h3><?php _e('So, how do I display a random quote on this blog?','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1",$widgetpage,__('Stray Random Quotes comes with <strong>Widgets</strong>. You can use all the widgets you want. Each widget has its own set of options. Just drag a "Quotes" widget to the sidebar on the <a href="%s1">widget page</a>, and change its settings accordingly.<br />Note: <em>Your template must be widget compatible.</em></p>','stray-quotes')); ?></p></blockquote>
     
        <?php // What if I don\'t use widgets?' ?>
        <h3><?php _e('What if I don\'t use widgets?','stray-quotes');?></h3>
        <blockquote><p><?php _e('If your template <strong>does not</strong> use widgets, or you want to display random quotes on your template <strong>elsewhere</strong> other than the sidebar, you can use <strong>template tags</strong>. These are PHP snippets that can be added directly to your template pages (in the header, the footer etc). You can use as many snippets as you need. They accept variables, but these have to be inserted in order. For example, if you want to set only the second variable, you have to set the first one too, but you can skip all those that follow if you don\'t need them.','stray-quotes');?></p>
        
        <p><strong><?php _e('To get one or more random quotes: ', 'stray-quotes');?>
        
        <code>&lt;?php if (function_exists('stray_random_quote')) stray_random_quote('category1,category2',false,'another quote &amp;raquo;',false,1,0,'quoteID','ASC'); ?&gt;</code></strong><br/><br/>
        
        <?php _e('Variables :', 'stray-quotes');?><br/>
        
        <ul>
        
        <li>1. <strong><code><?php _e('category1,category2', 'stray-quotes');?></code></strong> : <?php _e('A comma-separated list of the categories from which to extract the random quote. <code>\'all\'</code> or <code>\'\'</code> uses all the categories. Default is \'all\'.', 'stray-quotes');?></li>
        
        <li>2. <strong><code>true/false</code></strong> : <?php _e('Loads the quotes in order if "true", randomly if "false". Default is "false".', 'stray-quotes');?></li>
        
        <li>3. <strong><code><?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> : <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). If this value is empty, and the one in the settings is empty too, clicking on the quote area will reload the quote. Default is empty.', 'stray-quotes');?></li>
        
        <li>4. <strong><code>true/false</code></strong> <?php _e('Disables AJAX for the present template tag if "true". default is "false".', 'stray-quotes');?></li>
        
        <li>5. <strong><code>1</code></strong> :  <?php _e('How many quotes to retrieve at a time. Default is 1.', 'stray-quotes');?></li>
        
        <li>6. <strong><code>0</code></strong> : <?php _e('Reloads the quote automatically after the given interval (in seconds). Hides the linkphrase. Use 0 or \'\' to disable. Default is 0.', 'stray-quotes');?></li>
        
        <li>7. <strong><code>quoteID</code></strong> <?php _e('Orders the quotes according to "quoteID" or "Author" or "Source" or "Visible" or "Category". Default is "quoteID".', 'stray-quotes');?></li>
        
        <li>8. <strong><code>ASC</code></strong> <?php _e('Sorts the quotes ascending "ASC" or descending "DESC". Default is "ASC".', 'stray-quotes');?></li>
        
        </ul></p>
        
        <p><strong><?php _e('To get a specific quote: ', 'stray-quotes');?>
        
        <code>&lt;?php if (function_exists('stray_a_quote')) stray_a_quote(31,'Next quote &amp;raquo;',false); ?&gt;</code></strong><br/><br/>
        
        <?php _e('Variables :', 'stray-quotes');?><br/>
        
        <ul>
        
        <li>1. <strong><code><?php _e('31', 'stray-quotes');?></code></strong> : <?php _e('The ID number of the quote to be retrieved. Default is 1.', 'stray-quotes');?></li>
    
        <li>2. <strong><code><?php _e('Next quote &amp;raquo;', 'stray-quotes');?></code></strong> : <?php _e('The phrase to dynamically load the next quote in sequence. If this value is empty, and the one in the settings is empty too, clicking on the quote area will reload the quote. Default is empty.', 'stray-quotes');?></li>
        
        <li>3. <strong><code>true/false</code></strong> <?php _e('Disables AJAX for the present template tag if "true". default is "false".', 'stray-quotes');?></li>
        
        </ul></p><p><?php _e('Unlike widgets, template tags do not generate a title for the quote area. If you want specific titles for different tags, enter them directly in the template HTML, before the tag.' , 'stray-quotes'); ?>
        </p></blockquote>
        
        <?php // What about posts and pages? ?>
        <h3><?php _e('What about posts and pages?','stray-quotes');?></h3>
        <blockquote><p>
        
        <?php _e('Posts and pages accept <strong>shortcodes</strong>. Wordpress shortcodes can have many variables, in no particular order.<br/> They go like this:', 'stray-quotes');?><code>[sample-shortcode-name variable=value anothervariable=anothervalue]</code><br/><br/>
        
        <?php _e('To <strong>insert a random quote</strong> in a post or page use:', 'stray-quotes');?> <code><strong>[random-quote]</strong></code>   
        
        <br/><br/><?php _e('Variables (use quotations if there are spaces in the value):', 'stray-quotes');?><br/>
        
        <ul><li><strong><code>categories=cat1,cat2,etc</code></strong> <?php _e('A comma-separated list of the categories from which to extract the random quote. If none is set, quotes will be taken from all categories.', 'stray-quotes');?></li>
        
        <li><strong><code>sequence=true</code></strong> <?php _e('Loads the quotes in order if "true", randomly if "false". Default is "false"', 'stray-quotes');?></li>
        
        <li><strong><code>linkphrase=<?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). Without this value, clicking on the quote area will reload the quote.', 'stray-quotes');?></li>
        
        <li><strong><code>noajax=true</code></strong> <?php _e('Disables AJAX for the present shortcode if "true".', 'stray-quotes');?></li>
        
        <li><strong><code>timer=5</code></strong> <?php _e('Reloads the quote automatically after the given interval (in seconds). Hides the linkphrase. ', 'stray-quotes');?></li></ul>
        
        <br/><?php _e('All these settings are optional. If you don\'t indicate anything, a random quote from all the categories will be displayed, with ajax according to the general settings.<br/><br/>', 'stray-quotes');?></p>
        
        <p><?php _e('To <strong>insert more than one or all the quotes</strong> in a post or page use:', 'stray-quotes');?> <code><strong>[all-quotes]</strong></code>
        
        <br/><br/><?php _e('Variables (use quotations if there are spaces in the value):', 'stray-quotes');?><br/>
        
        <ul><li><strong><code>categories="cat1,cat2,etc"</code></strong> <?php _e('A comma-separated list of the categories from which to extract the random quote. If none is set, quotes will be taken from all categories.', 'stray-quotes');?></li>
        
        <li><strong><code>sequence=true</code></strong> <?php _e('Loads the quotes in order if "true", randomly if "false". Default is "false"', 'stray-quotes');?></li>
        
        <li><strong><code>linkphrase=<?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). Without this value, clicking on the quote area will reload the quote.', 'stray-quotes');?></li>
        
        <li><strong><code>rows=10</code></strong> <?php _e('How many quotes to retrieve at a time.', 'stray-quotes');?></li>
        
        <li><strong><code>orderby="quoteID"</code></strong> <?php _e('Orders the quotes according to "quoteID" or "Author" or "Source" or "Visible" or "Category".', 'stray-quotes');?></li>
        
        <li><strong><code>sort="ASC"</code></strong> <?php _e('Sorts the quotes ascending "ASC" or descending "DESC".', 'stray-quotes');?></li>
        
        <li><strong><code>fullpage=true</code></strong> <?php _e('Generates complete pagination links (with page numbers) if "true", only next and previous links if "false". Not considered if AJAX is enabled.', 'stray-quotes');?></li>
        
        <li><strong><code>noajax=true</code></strong> <?php _e('Disables AJAX for the present shortcode if "true".', 'stray-quotes');?></li>
        
        <li><strong><code>timer=5</code></strong> <?php _e('Reloads the quotes automatically after the given interval (in seconds). Hides the linkphrase. ', 'stray-quotes');?></li></ul>   
        
        <br/><?php _e('All these settings are optional. Without them, AJAX is disabled in the shortcode and the values you see in this example are used as defaults.', 'stray-quotes');?><br/><br/></p>
        
        
        <p><?php _e('To <strong>insert a specific quote</strong> in a post or page (with AJAX, a sequence of quotes starting from a given one) use:', 'stray-quotes');?> <code><strong>[quote id=x]</strong></code>
        
        <br/><br/><?php _e('Variables (use quotations if there are spaces in the value):', 'stray-quotes');?><br/>
        
        <ul><li><strong><code>id=12</code></strong> <?php _e('The ID of the quote to be retrieved. This number appears in the first column on the management page.', 'stray-quotes');?></li>
        
        <li><strong><code>noajax=true</code></strong> <?php _e('Disables AJAX for the present shortcode if "true". Default is true.', 'stray-quotes');?></li>
        
        <li><strong><code>linkphrase=<?php _e('"another quote &amp;raquo;"', 'stray-quotes');?></code></strong> <?php _e('The phrase to dynamically load another quote (overrides the one in the settings). Without this value, clicking on the quote area will reload the quote.', 'stray-quotes');?></li></ul>
        
        </blockquote>
        
        <?php // What about other areas of the blog, such as post titles, or even the blog title? ?>
        <h3><?php _e('What about other areas of the blog, such as post titles, or even the blog title?','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1",$toolspage,__('Well, actually, on the <a href="%s1">tools page</a> you can enable shortcodes for a number of extra areas where shortcodes aren\'t normally allowed. This will entitle you to some pretty extraordinary things, such as random taglines or random category names. It is <strong>highly recommended to disable AJAX</strong> for all these unconventional uses. Note that this will enable all shortcodes and not only those of Stray Random Quotes. More examples can be found <a href="http://www.italyisfalling.com/cool-things-you-can-do-with-stray-random-quotes">here</a>.', 'stray-quotes'));?></p></blockquote>
        
            <?php //How do I change the appearance of the quotes?  ?>
        <h3><?php _e('How do I change the appearance of the quotes?','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1",$options,__('To change how the quotes look, you can use the <a href="%s1">settings page</a>. Here you will be able to set what comes before and after every part of the quote area (source, author, the quote etc). With little HTML, all sorts of customizations are possible, such as assigning styles, set the alignment of text, specify font or font color, adding images, quotation marks and many other entities. Just remember to close all the tags you opened before a part.', 'stray-quotes'));?></p></blockquote>
                
        <?php // The IDs of the quotes are getting ridiculously high ?>
        <h3><?php _e('The IDs of the quotes are getting ridiculously high. Can I do something about it?','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1",$toolspage,__('It is the way MySQL works, gaps are not important in an index. Anyway, there is a workaround. On the <a href="%s1">tools page</a> there\'s a button to <strong>reset all quoteID numbers in your table</strong>, after which the numbering will look fine again. There is a downside, though: few or even none of the old ID numbers will still correspond to the same quotes, which means that if you called a quote in a post <strong>by its ID</strong> (as explained on this very page), there\'s a chance a different quote will be called afterwards. It is up to you to accept this and go and fix it later.', 'stray-quotes')); ?></p></blockquote>
        
        <?php // The bookmarklet ?>
        <h3><?php _e('I want a bookmarklet to quote things on the fly when I browse!','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1",$toolspage,__('You have it. It is on the <a href="%s1">tools page</a>. Place the link on your browser toolbar, select text in a page and click on the link. The "add new quote" page will open with the selected text in it.', 'stray-quotes')); ?></p></blockquote>
    
        <?php // Hey. There is a bug ?>
        <h3><?php _e('Hey. There is a bug!','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('I knew it... See, I am not a programmer or anything. There\'s always a bug. If you want to help me catch it, and for further help, please come and trash the comments <a href="%s1">on this post</a>. Thanks.<br /><br />', 'stray-quotes')); ?></p></blockquote>
        
        
        <?php // DONATION ?>
        <h3><?php _e('Anything I can do to help?','stray-quotes');?></h3>
        <blockquote><p><?php echo str_replace("%s1","http://www.italyisfalling.com/stray-random-quotes/",__('Well, yeah. If you have some spare change...', 'stray-quotes')); ?>
        
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="cmd" value="_s-xclick">
    <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
    ">
    <input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
    <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1"style="padding:0;border:none">
    </form>
        </p></blockquote>
    
    </div><?php

} ?>