wordpress-multi-part-posts
==========================

Multi Part Posts allows you to easily configure and organize posts with multiple parts, eg. "My Awesome Blog Part 1", "...Part 2", etc... by introducing a table of contents to the top of your post.

***

##Filters

###multi_part_post_types
This filter allows you to enable the plugin on custom post types.

####arguments
1. array of post types, default is `array('post')`

```
add_filter('multi_part_post_types','your_filter_function');
```

***

###display_before_post_multi_part
This filter enables/disables showing the table of contents at the top of a post

####arguments
1. boolean, default is `true`

```
add_filter('display_before_post_multi_part','your_filter_function');
```
    
***

###display_after_post_multi_part
This filter enables/disables showing the table of contents at the bottom of a post

####arguments
1. boolean, default is `false`

```
add_filter('display_after_post_multi_part','your_filter_function');
```
    
***

###multi_part_markup
This filter enables you to customize the output on the front end

####arguments
1. default markup
2. array of post objects for the table of contents

```
add_filter('multi_part_markup,'your_filter_function',10,2);
```