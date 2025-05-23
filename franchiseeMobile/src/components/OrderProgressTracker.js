import React from 'react';
import { View, Text, StyleSheet } from 'react-native';
// Using react-native-vector-icons instead of Expo icons
import FontAwesome from 'react-native-vector-icons/FontAwesome';

const OrderProgressTracker = ({ status }) => {
  // Setup step data
  const steps = [
    { id: 'pending', label: 'Pending', icon: 'clipboard-check' },
    { id: 'processing', label: 'Approved', icon: 'cogs' },
    { id: 'packed', label: 'Packed', icon: 'box' },
    { id: 'shipped', label: 'Shipped', icon: 'truck' }, // Changed from shipping-fast to truck
    { id: 'delivered', label: 'Delivered', icon: 'check-circle' }
  ];

  // Calculate progress width based on order status
  const calculateProgress = () => {
    // Default values
    let progressWidth = 0;
    let progressColor = '#4CAF50'; // Default green

    // Calculate position for each status
    const stepWidth = 100 / (steps.length - 1); // Stepwidth for positions (0%, 25%, 50%, 75%, 100%)

    switch(status) {
      case 'pending':
        progressWidth = stepWidth * 0; // 0%
        break;
      case 'processing':
      case 'approved':
        progressWidth = stepWidth * 1; // 25%
        break;
      case 'packed':
        progressWidth = stepWidth * 2; // 50%
        break;
      case 'shipped':
        progressWidth = stepWidth * 3; // 75%
        break;
      case 'delivered':
        progressWidth = stepWidth * 4; // 100%
        break;
      case 'rejected':
      case 'cancelled':
        progressWidth = stepWidth * 1; // 25% (at the approved stage)
        progressColor = '#dc3545'; // Red for rejected/cancelled
        break;
      default:
        progressWidth = 0;
    }

    return { width: progressWidth, color: progressColor };
  };

  // Determine status of each step
  const getStepStatus = (stepId) => {
    if (status === 'rejected' || status === 'cancelled') {
      if (stepId === 'processing') {
        return 'rejected';
      }
      
      if (stepId === 'pending') {
        return 'completed';
      }
      
      return 'inactive';
    }
    
    const statusOrder = ['pending', 'processing', 'packed', 'shipped', 'delivered'];
    const currentIndex = statusOrder.indexOf(status);
    const stepIndex = statusOrder.indexOf(stepId);
    
    if (stepIndex < 0) {
      return 'inactive';
    }
    
    if (stepIndex === currentIndex) {
      return 'active';
    }
    
    if (stepIndex < currentIndex) {
      return 'completed';
    }
    
    return 'inactive';
  };

  // Map FontAwesome icon names to icons available in react-native-vector-icons/FontAwesome
  const getIconName = (originalIcon, stepStatus) => {
    // Special case for rejected status
    if (stepStatus === 'rejected') {
      return 'times';
    }
    
    // Map custom icon names to available FontAwesome icons
    const iconMap = {
      'clipboard-check': 'clipboard',
      'cogs': 'cog',
      'box': 'archive',
      'shipping-fast': 'truck',
      'check-circle': 'check-circle'
    };
    
    return iconMap[originalIcon] || originalIcon;
  };
  
  // Calculate progress style
  const progress = calculateProgress();

  return (
    <View style={styles.container}>
      <View style={styles.orderTracker}>
        {/* Progress line that fills based on order status */}
        <View 
          style={[
            styles.progressLine, 
            { 
              width: `${progress.width}%`,
              backgroundColor: progress.color 
            }
          ]} 
        />
        
        {/* Tracker steps */}
        {steps.map(step => {
          const stepStatus = getStepStatus(step.id);
          const iconName = getIconName(step.icon, stepStatus);
          
          return (
            <View key={step.id} style={styles.trackerStep}>
              <View 
                style={[
                  styles.stepIcon,
                  stepStatus === 'active' && styles.activeStepIcon,
                  stepStatus === 'completed' && styles.completedStepIcon,
                  stepStatus === 'rejected' && styles.rejectedStepIcon
                ]}
              >
                <FontAwesome 
                  name={iconName}
                  size={12} 
                  color={stepStatus !== 'inactive' ? '#fff' : '#888'} 
                />
              </View>
              <Text 
                style={[
                  styles.stepLabel,
                  stepStatus === 'active' && styles.activeStepLabel,
                  stepStatus === 'completed' && styles.completedStepLabel,
                  stepStatus === 'rejected' && styles.rejectedStepLabel
                ]}
              >
                {step.id === 'processing' && (status === 'rejected' || status === 'cancelled') 
                  ? (status === 'rejected' ? 'Rejected' : 'Cancelled')
                  : step.label
                }
              </Text>
            </View>
          );
        })}
      </View>
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    marginVertical: 12,
  },
  orderTracker: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
    position: 'relative',
    height: 60, // Reduced height
    paddingHorizontal: 10,
  },
  progressLine: {
    position: 'absolute',
    height: 2, // Thinner line
    backgroundColor: '#4CAF50',
    top: 18, // Positioned at the center of the icons
    left: 0,
    zIndex: 1,
    borderRadius: 1,
  },
  trackerStep: {
    alignItems: 'center',
    zIndex: 2,
    width: '20%', // 5 equal steps
  },
  stepIcon: {
    width: 36, // Smaller circles
    height: 36, // Smaller circles
    borderRadius: 18, // Half of width/height
    backgroundColor: '#fff',
    borderWidth: 2, // Thinner border
    borderColor: '#e5e5e5',
    justifyContent: 'center',
    alignItems: 'center',
    marginBottom: 5, // Less space below
    zIndex: 3,
  },
  stepLabel: {
    fontSize: 10, // Smaller font
    color: '#888',
    textAlign: 'center',
    fontWeight: '500',
    maxWidth: '100%', // Ensure text doesn't overflow container width
  },
  activeStepIcon: {
    backgroundColor: '#4CAF50',
    borderColor: '#4CAF50',
    elevation: 4,
  },
  activeStepLabel: {
    color: '#4CAF50',
    fontWeight: 'bold',
  },
  completedStepIcon: {
    backgroundColor: '#4CAF50',
    borderColor: '#4CAF50',
  },
  completedStepLabel: {
    color: '#4CAF50',
  },
  rejectedStepIcon: {
    backgroundColor: '#dc3545',
    borderColor: '#dc3545',
    elevation: 4,
  },
  rejectedStepLabel: {
    color: '#dc3545',
    fontWeight: 'bold',
  },
});

export default OrderProgressTracker;