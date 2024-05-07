<?php

/**
 * This code was generated by
 * \ / _    _  _|   _  _
 * | (_)\/(_)(_|\/| |(/_  v1.0.0
 * /       /
 */

namespace Twilio\Rest\Taskrouter\V1\Workspace\Worker;

use Twilio\InstanceContext;
use Twilio\Options;
use Twilio\Serialize;
use Twilio\Values;
use Twilio\Version;

class WorkerChannelContext extends InstanceContext
{
    /**
     * Initialize the WorkerChannelContext
     *
     * @param \Twilio\Version $version      Version that contains the resource
     * @param string          $workspaceSid The workspace_sid
     * @param string          $workerSid    The worker_sid
     * @param string          $sid          The sid
     *
     * @return \Twilio\Rest\Taskrouter\V1\Workspace\Worker\WorkerChannelContext
     */
    public function __construct(Version $version, $workspaceSid, $workerSid, $sid)
    {
        parent::__construct($version);

        // Path Solution
        $this->solution = array('workspaceSid' => $workspaceSid, 'workerSid' => $workerSid, 'sid' => $sid,);

        $this->uri = '/Workspaces/'.rawurlencode($workspaceSid).'/Workers/'.rawurlencode($workerSid).'/Channels/'.rawurlencode($sid).'';
    }

    /**
     * Fetch a WorkerChannelInstance
     *
     * @return WorkerChannelInstance Fetched WorkerChannelInstance
     */
    public function fetch()
    {
        $params = Values::of(array());

        $payload = $this->version->fetch(
            'GET',
            $this->uri,
            $params
        );

        return new WorkerChannelInstance(
            $this->version,
            $payload,
            $this->solution['workspaceSid'],
            $this->solution['workerSid'],
            $this->solution['sid']
        );
    }

    /**
     * Update the WorkerChannelInstance
     *
     * @param array|Options $options Optional Arguments
     *
     * @return WorkerChannelInstance Updated WorkerChannelInstance
     */
    public function update($options = array())
    {
        $options = new Values($options);

        $data = Values::of(array(
            'Capacity'  => $options['capacity'],
            'Available' => Serialize::booleanToString($options['available']),
        ));

        $payload = $this->version->update(
            'POST',
            $this->uri,
            array(),
            $data
        );

        return new WorkerChannelInstance(
            $this->version,
            $payload,
            $this->solution['workspaceSid'],
            $this->solution['workerSid'],
            $this->solution['sid']
        );
    }

    /**
     * Provide a friendly representation
     *
     * @return string Machine friendly representation
     */
    public function __toString()
    {
        $context = array();
        foreach ($this->solution as $key => $value) {
            $context[] = "$key=$value";
        }
        return '[Twilio.Taskrouter.V1.WorkerChannelContext '.implode(' ', $context).']';
    }
}