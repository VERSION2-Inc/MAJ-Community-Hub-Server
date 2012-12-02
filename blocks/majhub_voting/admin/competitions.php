<?php // $Id: competitions.php 148 2012-12-01 08:37:33Z malu $

require_once __DIR__.'/../../../config.php';
require_once __DIR__.'/../classes/competition.php';

if (false) {
    $CFG    = new stdClass;
    $SITE   = new stdClass;
    $DB     = new mysqli_native_moodle_database;
    $OUTPUT = new core_renderer;
    $PAGE   = new moodle_page;
}

require_once $CFG->libdir.'/adminlib.php';
require_once $CFG->libdir.'/tablelib.php';
require_once $CFG->libdir.'/formslib.php';

class competition_form extends moodleform
{
    public function definition()
    {
        $editid = $this->_customdata;

        $this->_form->addElement('header', '',
            get_string($editid > 0 ? 'editcompetition' : 'addcompetition', 'block_majhub_voting')
            );

        $this->_form->addElement('text', 'title', get_string('title', 'block_majhub_voting'), array('size' => 30));
        $this->_form->addElement('text', 'description', get_string('description', 'block_majhub_voting'), array('size' => 50));
        $this->_form->addElement('date_selector', 'timestart', get_string('startdate', 'block_majhub_voting'));
        $this->_form->addElement('date_selector', 'timeend', get_string('enddate', 'block_majhub_voting'));

        $this->_form->addRule('title', get_string('required'), 'required', null, 'client');
        $this->_form->addRule('timestart', get_string('required'), 'required', null, 'client');
        $this->_form->addRule('timeend', get_string('required'), 'required', null, 'client');

        if ($editid > 0) {
            $competition = majhub\voting\competition::from_id($editid, MUST_EXIST);
            $this->_form->addElement('hidden', 'id', $competition->id);
            $this->_form->setDefault('title', $competition->title);
            $this->_form->setDefault('description', $competition->description);
            $this->_form->setDefault('timestart', $competition->timestart);
            $this->_form->setDefault('timeend', $competition->timeend);
            $this->add_action_buttons(true, get_string('update'));
        } else {
            $this->_form->setDefault('timestart', mktime(0, 0, 0, date('n') + 1, 1));
            $this->_form->setDefault('timeend', mktime(0, 0, 0, date('n') + 2, 0));
            $this->add_action_buttons(true, get_string('add'));
        }
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);
        if (empty($data['timestart']) || empty($data['timeend']) || $data['timestart'] >= $data['timeend'])
            $errors['timeend'] = get_string('error:competition:invalidperiod', 'block_majhub_voting');
        return $errors;
    }
}

require_login(null, false); // no guest autologin

$section = 'blocksetting' . 'majhub_voting';
$baseurl = new moodle_url('/blocks/majhub_voting/admin/competitions.php');

$editid   = optional_param('id', 0, PARAM_INT);
$deleteid = optional_param('delete', 0, PARAM_INT);

$PAGE->set_context(context_system::instance());
$PAGE->set_url('/admin/settings.php', array('section' => $section));
$PAGE->set_pagetype('admin-setting-' . $section);
$PAGE->set_pagelayout('admin');

$adminroot = admin_get_root();
$settingspage = $adminroot->locate($section, true);
if (empty($settingspage) || !($settingspage instanceof admin_settingpage)) {
    //print_error('sectionerror', 'admin', "$CFG->wwwroot/$CFG->admin/");
    print_error('accessdenied', 'admin');
}
if (!$settingspage->check_access()) {
    print_error('accessdenied', 'admin');
}

$pathtosection = array_reverse($settingspage->visiblepath);
$strpagetitle = get_string('settings/competitions', 'block_majhub_voting');
$PAGE->set_title($SITE->shortname . ': ' . implode(': ', $pathtosection) . ': ' . $strpagetitle);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add($strpagetitle, $baseurl);
$PAGE->requires->css('/blocks/majhub_voting/admin/competitions.css');

$mform = new competition_form($baseurl, $editid);
if ($data = $mform->get_data()) {
    majhub\voting\competition::from_record($data)->save();
    redirect($baseurl);
} elseif ($mform->is_cancelled()) {
    redirect($baseurl);
}

if ($deleteid && confirm_sesskey()) {
    majhub\voting\competition::from_id($deleteid, MUST_EXIST)->delete();
    redirect($baseurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('competitions', 'block_majhub_voting'));

$competitions = $DB->get_records(majhub\voting\competition::TABLE, null, 'timestart ASC');

$strtitle       = get_string('title', 'block_majhub_voting');
$strdescription = get_string('description', 'block_majhub_voting');
$strstartdate   = get_string('startdate', 'block_majhub_voting');
$strenddate     = get_string('enddate', 'block_majhub_voting');
$dateformat     = get_string('strftimedateshort', 'langconfig');

$table = new flexible_table('block_majhub_voting-competitions');
$table->define_baseurl($baseurl);
$table->define_columns(array('title', 'description', 'startdate', 'enddate', 'actions'));
$table->define_headers(array($strtitle, $strdescription, $strstartdate, $strenddate, get_string('actions')));
$table->column_class('actions', 'actions');
$table->setup();
foreach ($competitions as $competition) {
    $actions = array(
        $OUTPUT->action_icon(
            new moodle_url($baseurl, array('id' => $competition->id)),
            new pix_icon('t/edit', get_string('edit'))
            ),
        $OUTPUT->action_icon(
            new moodle_url($baseurl, array('delete' => $competition->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', get_string('delete')),
            new confirm_action(get_string('confirm:deletecompetition', 'block_majhub_voting'))
            ),
        );
    $table->add_data(
        array(
            $competition->title,
            $competition->description,
            userdate($competition->timestart, $dateformat),
            userdate($competition->timeend, $dateformat),
            $competition->id == $editid ? '' : implode(' ', $actions),
            ),
        $competition->id == $editid ? 'editing' : ''
        );
}
$addaction = $OUTPUT->action_icon(
    new moodle_url($baseurl, array('id' => -1)), new pix_icon('t/add', get_string('add'))
    );
$table->add_data(array('', '', '', '', $editid == -1 ? '&nbsp;' : $addaction), $editid == -1 ? 'editing' : '');
$table->print_html();

if ($editid != 0) {
    echo $mform->display();
}

echo $OUTPUT->footer();
