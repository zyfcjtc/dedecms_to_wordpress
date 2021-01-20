# 织梦CMS转wordpress详细步骤
## 导出织梦Dedecms全站RSS文件
### 步骤
1. 在dedecms的dede文件夹（后台文件夹）下找到`makehtml_rss_action.php`文件，对其进行编辑，找到代码：
    ```php
    echo "完成所有文件更新！";
    ```
      在其下面添加代码：
    ```php
    echo "<a href='/rss.xml' target='_blank'>浏览…</a>";
    ```
    作用是在生成rss.xml文件后方便点击查看生成结果。
2. 在dedecms的`include`文件夹下找到`arc.rssview.class.php`文件，并对其进行编辑，找到代码：
    ```php
    $murl = $GLOBALS['cfg_cmspath']."/data/rss/".$this->TypeID.".xml";
    ```
    修改为：
    ```php
    $murl = $GLOBALS['cfg_cmspath']."/rss.xml";
    ```
    作用是修改生成的`rss.xml`文件路径，让该文件保存在网站根目录
    
    再向下找到以下代码并将其删除：
    ```php
    $orwhere .= "And (arc.typeid in (".GetSonIds($this->TypeID,$this->TypeFields['channeltype']).") )";
    ```
3.  进入后台——生成——更新RSS文件，把“单个类目最大记录数”改为全站的文章数量，然后点击“开始更新”，更新完成后，网站根目录下会生成一个全站rss.xml文件，可通过 `http://网站域名/rss.xml`     查看。
    附：如果生成的rss.xml文件有错误，在根目录下的templets文件夹中的plus文件夹里找到rss.htm文件，对其进行编辑，找到代码：
    ```php
    <description><![CDATA[[field:description function='html2text(@me)'/]]]></description>
    ```
    修改为：
    ```php
    <description><![CDATA[[field:description/]]]></description>
    ```
## 导入织梦Dedecms全站RSS文件
注：wordpress的编码是utf8，如果dedecms使用的不是utf8的，导入前请先转换编码为utf8。
具体如何查看：
找到`common.inc.php`文件，里面的
```php
$cfg_version = 
$cfg_soft_lang = 
$cfg_soft_public = 
```
即可知道是什么编码
### 导入织梦RSS步骤
1.  织梦DedeCMS文章标题等基本数据导入wordpress站点
    进入wordpress后台admin => 工具 => 导入 => RSS, 上传导入生成的织梦全站RSS文件。
    导入过程中有可能会超时，重新上传RSS即可，不会重复导入。
    注：我通过修改`rss-import.php`里的`get_posts function`，实现了同时导入织梦文章id到wordpress.
2.  导入织梦CMS文章全文到wordpress站点
    织梦文章的数据存储在dede_addonarticle数据库的body字段中，现在需要把body字段的内容转到wordpress数据库的wp_posts数据库的         post_content字段里。

    这个转换需要使用一个桥梁——那就是dede_archives数据表，即dede_addonarticle上body的内容先转到dede_archives上，再从               dede_archives转到wp_posts的post_content里。这两次转换的匹配点，分别是织梦里的文章id，以及Wordpress里已经导入了的文章标题       （这与织梦里的文章标题是一样的）。
    
    具体步骤如下：
    进入phpmyadmin，选择dedecms网站使用的数据库，在SQL输入框中执行以下SQL语句，在织梦数据库的dede_archives表上，添加字段body
    ```php
    ALTER TABLE dede_archives ADD body longtext NOT NULL
    ```
    然后再执行以下SQL语句把dede_addonarticle数据表中的body字段内容导入到dede_archives的body字段，语句以dede_addonarticle的aid     和dede_archives的id为匹配点：
    ```php
    UPDATE dede_archives,dede_addonarticle
    SET dede_archives.body = dede_addonarticle.body  
    WHERE dede_archives.id = dede_addonarticle.aid
    ```
    接着通过phpmyadmin导出功能把dede_archives数据表导出，然后再通过导入功能把该数据表导入wordpress网站使用的数据库中，使其与       wp_posts数据库处在同一个数据库里。

    下面再次使用SQL语句把dede_archives的body导入到wp_posts上的post_content上，~~以文章标题为匹配点（前提是文章标题都是唯一的）~~ 以ID为匹配点：
    ```php
    UPDATE wp_posts,dede_archives  
    SET wp_posts.post_content = dede_archives.body  
    WHERE wp_posts.id = dede_archives.id
    ```
    至此文章内容部分转换完成！
