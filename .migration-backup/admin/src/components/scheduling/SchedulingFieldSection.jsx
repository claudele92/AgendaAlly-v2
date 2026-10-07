import React from 'react';
import { Col } from 'antd';
import './schedule-surface.css';

const SchedulingFieldSection = ({ label = 'Scheduling options', children }) => (
  <Col span={24} className="aa-scheduling-section-column">
    <section className="aa-scheduling-section" aria-label={label}>
      <div className="aa-scheduling-section__fields">{children}</div>
    </section>
  </Col>
);

export default SchedulingFieldSection;