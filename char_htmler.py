import lxml, lxml.etree, os, time, re
import mechanize

u = "YOUR NAME HERE"
p = "YOUR PASS HERE"
path = "YOUR FILE HERE"

ns= {"x":'http://www.w3.org/1999/xhtml'}
if not os.path.exists(out):
    os.mkdir(out)

tree = lxml.etree.parse(path)
browser = mechanize.Browser(factory=mechanize.RobustFactory())

def login_to_compendium (b):
    login = "http://www.wizards.com/dndinsider/compendium/login.aspx"
    b.open(login)
    b.select_form('form1')
    if not b.form.find_control("email"):
        email = mechanize.TextControl('text','email',{})
        email.add_to_form(b.form)
    b["email"] = u
    b["password"] = p
    try:
        b.submit()
    except:
        #ends in a 404 which is an error
        pass

login_to_compendium(browser)

class GameItem:
    def __init__(self, name, url, content=None, browser=browser):
        self.name = name
        self.url = url
        self.content = content
        self.browser = browser
        self.filtered_content = None
        self.path = os.path.join(out, self.name+".html")

    def _pull_compendium (self):
        time.sleep(1)
        b = self.browser
        b.open(self.url)
        self.content = b.response().read()
        tree = lxml.etree.fromstring(self.content.replace("&nbsp;"," "))
        el = tree.xpath("//x:div[@id='detail']", namespaces=ns)[0]
        self.filtered_content = lxml.etree.tostring(el)

    def _write_static_content(self):
        if self.content:
            with open(self.path, "w") as f:
                f.write(self.content)

    def save_compendium(self):
        print "Retrieving: " + self.name
        self._pull_compendium()
        #self._write_static_content()
        
def get_rituals():
    rituals_xpath = "//LootTally/loot/RulesElement[@type='Ritual']"
    rituals_elems = tree.xpath(rituals_xpath)
    rituals =[]
    for e in rituals_elems:
        if e.attrib.get("url"):
            gi = GameItem(e.attrib.get("name"), e.attrib.get("url"))
            rituals.append(gi);
            gi.save_compendium()
    return rituals

_dom_id_re = re.compile(r"('|,|\.|\\|/|\s)+")
def dom_id (text):
    return _dom_id_re.subn("_",text)[0]
    
def write_html_char_sheet(rituals=None):
    if not rituals:
        rituals = get_rituals()
    with open(os.path.join(out, path+".html"), "w") as f:
        body = ""
        f.write("<html><head><title>"+path+"""</title>
<style>
@import url(site.css);

#detail { background: #fff; float: left; padding: 15px; width: 560px; color: #3e141e; }
#detail { font-size: 0.916em; }
#detail p { padding-left: 15px; color: #3e141e; }
#detail table { width: 100%; }
#detail table td { vertical-align: top; padding: 0 10px 0; background: #d6d6c2; border-bottom: 1px solid #fff; }
#detail p.flavor, #detail span.flavor, #detail ul.flavor { display: block; padding: 2px 15px; margin: 0; background: #d6d6c2; }
#detail p.powerstat { padding: 0px 0px 0px 15px; margin: 0; background: #FFFFFF; }
#detail span.ritualstats { float:right; padding: 0 30px 0 0; }
#detail p.flavorIndent { display: block; padding: 2px 15px 2px 30px; margin: 0; background: #d6d6c2; }
#detail p.alt, #detail span.alt, #detail td.alt { background: #c3c6ad; }
#detail th { background: #1d3d5e; color: #fff; text-align: left; padding: 0 0 0 5px; }
#detail i, #detail em { font-style: italic; } 
#detail ul { list-style: disc; margin: 1em 0 1em 30px; }
#detail table, #detail ul.flavor { margin-bottom: 1em; }
#detail ul li { color: #3e141e; }
#detail ul.flavor li { list-style-image: url("../images/bullet.gif"); margin-left: 15px; }
#detail a { color: #3e141e; }
#detail blockquote { padding: 0 0 0 22px; background: #d6d6c2; }
#detail h1 { font-size: 1.09em; line-height: 2; padding-left: 15px; margin: 0; color: #fff; background: #000; }
#detail h1.player { background: #1d3d5e;
    font-size: 1.35em; }
#detail h1.monster { background: #4e5c2e;
    height:38px; }
#detail h1.dm { background: #5c1f34;
}
#detail h1.trap { background: #5c1f34;
    height:38px; }
#detail h1.atwillpower { background: #619869;
}
#detail h1.encounterpower { background: #961334;
}
#detail h1.dailypower { background: #4d4d4f;
}
#detail h1.magicitem { background: #d8941d;
}
#detail h1.utilitypower { background: #1c3d5f;
}
#detail h1 .level { padding-right: 15px;
	margin-top: 0; text-align: right;
	float: right; }
#detail h1.monster .level, h1.trap .level { margin-top: 0;
	text-align: right; position:relative;
	top:-60px; }
#detail h1.monster .type, #detail h1.monster .xp {
	display: block; position: relative;
	z-index: 99; top: -0.75em;
	height: 1em; font-weight: normal;
	font-size: 0.917em; }
#detail .rightalign { text-align: right;
}
/* Traps */ #detail h1.trap .level {
	margin-top: 0; text-align: right;
}
#detail h1.trap .type, #detail h1.trap .xp {
	display: block; position: relative;
	z-index: 99; top: -0.75em;
	height: 1em; font-weight: normal;
	font-size: 0.917em; }
#detail .traplead { display: block;
	padding: 1px 15px; margin: 0;
	background: #ffffff; }
#detail .trapblocktitle { display: block;
	padding: 1px 15px; margin: 0;
	background: #d6d6c2; font-weight: bold;
}
#detail .trapblockbody { display: block;
	padding: 1px 15px 1px 30px; margin: 0;
	background: #ffffff; }
 /* Detail page related link section */
/* -------------------------------------------- */ 
#detail #RelatedArticles h5 { width: 100px;
	float: left; padding-top: 10px;
	padding-left: 20px; color: #3e141e;
	font-weight: bold; }
#detail #RelatedArticles ul.RelatedArticles { padding: 10px 0 0 0;
	float: right; width: 430px;
	margin: 0; list-style: none;
}
 #detail .bodytable {
	border: 0; margin: 0;
	width: 560px; background: #d6d6c2;
}
#detail .bodytable td { border-bottom: none;
	padding-left: 15px; padding-right: 15px;
}
#detail h2 { font-size: 1.25em;
    padding-left: 15px; margin: 0;
    color: #fff; background: #4e5c2e;
    height:20px; font-variant: small-caps;
    padding-top: 5px; }
</style>
</head><body>\n<ul>""")
        for r in rituals:
            f.write('<li><a href="#%s">%s</a></li>' % (dom_id(r.name), r.name))
            body += '<div id="'+dom_id(r.name)+'" class="ritual">'+ \
                r.filtered_content+'<div style="clear:both;"> </div></div><div style="clear:both;"><br /> </div>\n\n'
        
        f.write("</ul>")
        f.write(body)
        f.write("</body></html>")
        

