<?php
namespace block_notes;
class label implements \renderable, \templatable {
    /**
     * The unique label id
     *
     * @var string
     */
    protected $id;

    /**
     * The user id
     *
     * @var string
     */
    protected $userid;

    /**
     * The course id
     *
     * @var string
     */
    protected $courseid;

    /**
     * The text name of the label
     *
     * @var string
     */
    protected $name;

    /**
     * The publish date of the item in Unix timestamp format
     *
     * @var int
     */
    protected $timemodified;

    /**
     * label_item constructor.
     * @param $id
     * @param $userid
     * @param $courseid
     * @param $name
     * @param $timemodified
     */
    public function __construct($id, $userid, $courseid, $name, $timemodified){
        $this->id = $id;
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->name = $name;
        $this->timemodified = $timemodified;
    }

    /**
     * Export context for use in mustache templates
     *
     * @see templatable::export_for_template()
     * @param renderer_base $output
     * @return array
     */
    public function export_for_template(\renderer_base $output) {
        $data = array(
            'id'            => $this->id,
            'name'          => $this->name,
            'datepublished' => $output->format_published_date($this->timemodified)
        );

        return $data;
    }

    /**
     * @return string
     */
    public function get_id(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return label_item
     */
    public function set_id(string $id): label_item
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function get_userid(): string
    {
        return $this->userid;
    }

    /**
     * @param string $userid
     * @return label_item
     */
    public function set_userid(string $userid): label_item
    {
        $this->userid = $userid;
        return $this;
    }

    /**
     * @return string
     */
    public function get_courseid(): string
    {
        return $this->courseid;
    }

    /**
     * @param string $courseid
     * @return label_item
     */
    public function set_courseid(string $courseid): label_item
    {
        $this->courseid = $courseid;
        return $this;
    }

    /**
     * @return string
     */
    public function get_name(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return label_item
     */
    public function set_name(string $name): label_item
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int
     */
    public function get_timemodified(): int
    {
        return $this->timemodified;
    }

    /**
     * @param int $timemodified
     * @return label_item
     */
    public function set_timemodified(int $timemodified): label_item
    {
        $this->timemodified = $timemodified;
        return $this;
    }


}